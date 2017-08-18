<?php

use CustomerManagementFrameworkBundle\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use Website\Auth\Adapter\Customer as CustomerAuthAdapter;
use Website\Auth\AuthService;
use Website\Auth\RegistrationFormHandler;
use Website\Controller\Action;

/**
 * @method Zend_Controller_Request_Http getRequest()
 */
class AuthController extends Action
{
    /**
     * @var AuthService
     */
    protected $authService;

    public function init()
    {
        parent::init();

        $this->enableLayout();
        $this->setLayout('auth');

        // start the session before HybridAuth does it as Zend_Session needs control over initialization
        Zend_Session::start(true);

        // handles login state
        $this->authService = new AuthService();
    }

    /**
     * Just an index page redirecting to login
     *
     * /auth/index
     */
    public function indexAction()
    {
        $this->redirect($this->view->url([
            'action' => 'login'
        ], 'auth', true));
    }

    /**
     * /auth/login
     */
    public function loginAction()
    {
        // redirects to the secure action
        $redirect = function () {
            $this->redirect($this->view->url([
                'action' => 'secure'
            ], 'auth', true));
        };

        // redirect to secure action if already logged in
        if ($this->authService->isLoggedIn()) {
            $redirect();

            return;
        }

        // handle form login
        $errors = [];
        if ($this->getRequest()->isPost()) {
            $form = $this->buildLoginForm();
            if ($form->isValid($this->getRequest()->getParams())) {

                // validate username/password auth with auth adapter
                $adapter      = new CustomerAuthAdapter($form->getValue('email'), $form->getValue('password'));
                $authResponse = $adapter->authenticate();

                if ($authResponse->getCode() === Zend_Auth_Result::SUCCESS) {
                    $this->authService->login($authResponse->getIdentity());
                    $redirect();
                }

                if (null === $authResponse || $authResponse->getCode() !== Zend_Auth_Result::SUCCESS) {
                    $errors[] = 'Invalid credentials';
                }
            } else {
                $errors = $this->addFormErrors($errors, $form);
            }
        }

        $this->view->errors = $errors;
    }

    /**
     * /auth/logout
     */
    public function logoutAction()
    {
        if ($this->authService->isLoggedIn()) {
            $this->authService->logout();
        }

        $this->redirect($this->view->url([
            'action' => 'login'
        ], 'auth', true));
    }

    /**
     * Registration can either be called with a provider parameter or without. If a provider is passed, it will try
     * to read a SSO identity for the given provider from the HybridAuth store. This identity will then be added to the
     * customer profile and will pre-fill customer data (e.g. name if given).
     *
     * /auth/register
     */
    public function registerAction()
    {
        $registrationFormHandler = new RegistrationFormHandler();

        // create a new, empty customer instance
        /** @var \CustomerManagementFrameworkBundle\Model\CustomerInterface|\Pimcore\Model\Object\Customer $customer */
        $customer = \Pimcore::getContainer()->get('cmf.customer_provider')
            ->create();

        /** @var SsoIdentityInterface $ssoIdentity */
        $ssoIdentity = null;

        // we come from an SSO provider - try to load the SSO identity
        if ($this->getParam('provider')) {
            // call authenticate again - if we're logged in the auth handler will keep auth result
            // in its session and not authenticate again
            $hybridAuthHandler = $this->authenticateHybridAuth();

            // try to load a customer with the given identity from our storage. if this succeeds, we can't register the customer
            // and should either log in the existing identity or show an error. for simplicity, we just throw an exception.
            if ($hybridAuthHandler->getCustomerFromAuthResponse($this->getRequest())) {
                throw new RuntimeException('Customer is already registered');
            }

            // update customer to be registered with auth response (SSO identity, profile data)
            /** @var \Pimcore\Model\Object\SsoIdentity $ssoIdentity */
            $ssoIdentity = $hybridAuthHandler->updateCustomerFromAuthResponse($customer, $this->getRequest());
        }

        // build the registration form and pre-fill it with customer data
        $form   = $registrationFormHandler->buildRegistrationForm($customer);
        $errors = [];

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getParams())) {
                $registrationFormHandler->mapFormValuesToCustomer($form->getValues(), $customer);

                try {
                    $customer->setKey(\Pimcore\File::getValidFilename($customer->getEmail()));
                    $customer->save();

                    // add SSO identity to customer object
                    if (null !== $ssoIdentity) {
                        // fix getParentId() still resolving to 0 as it was set
                        // when unsaved customer was passed as parent - this isn't necessary after Pimcore 4.4.2
                        $ssoIdentity->setParent($customer);
                        $ssoIdentity->save();

                        // set SSO identity on customer as this couldn't be done by auth handler before both objects were saved
                        $ssoIdentityService = Pimcore::getDiContainer()->get(SsoIdentityServiceInterface::class);
                        $ssoIdentityService->addSsoIdentity($customer, $ssoIdentity);
                        $customer->save();
                    }

                    // customer object is ready, now log in and redirect to secure action
                    $this->authService->login($customer);

                    $this->redirect($this->view->url([
                        'action' => 'secure'
                    ], 'auth', true));
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            } else {
                $errors = $this->addFormErrors($errors, $form);
            }
        }

        $this->view->customer = $customer;
        $this->view->form     = $form;
        $this->view->errors   = $errors;
    }

    /**
     * This actions starts the SSO login by passing a provider parameter which is passed to HybridAuth. If the given
     * provider is configured, HybridAuth will redirect to the given service login page.
     *
     * /auth/hybridauth
     */
    public function hybridauthAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        /** @var \CustomerManagementFrameworkBundle\Authentication\Sso\DefaultHybridAuthHandler $hybridAuthHandler */
        $hybridAuthHandler = null;

        try {
            $hybridAuthHandler = $this->authenticateHybridAuth();
        } catch (\CustomerManagementFrameworkBundle\Authentication\AuthenticationException $e) {
            \Pimcore\Logger::warning('Failed to log in via SSO: ' . $e->getMessage());

            $this->redirect($this->view->url([
                'action' => 'login'
            ], 'auth', false));

            return;
        }

        // SSO login succeeded - try to get a local customer with the returned SSO identity
        $customer = $hybridAuthHandler->getCustomerFromAuthResponse($this->getRequest());

        if ($this->authService->isLoggedIn()) {
            // if there's a customer in the session and a customer is found from the SSO identity, abort if they don't match
            if ($customer) {
                if ($customer->getId() !== $this->authService->getCustomer()->getId()) {
                    throw new RuntimeException('We have a logged in customer and found a customer from the SSO identity, but do not match');
                }
            } else {
                // customer was logged in and a new SSO identity not used elsewhere was returned -> link it to the current customer

                // update an already logged in customer with the auth identity
                $customer = $this->authService->getCustomer();

                $ssoIdentity = $hybridAuthHandler->updateCustomerFromAuthResponse($customer, $this->getRequest());
                $ssoIdentity->save();

                $customer->save();
            }
        }

        // if we have a customer, redirect to the secure page, otherwise proceed to registration
        if ($customer) {
            $this->authService->login($customer);
            $this->redirect($this->view->url([
                'action' => 'secure'
            ], 'auth', true));
        } else {
            $this->redirect($this->view->url([
                'action' => 'register'
            ], 'auth', false));
        }
    }

    /**
     * Fetches the HybridAuth handler from the DI container and starts authenticating. If the user already authenticated
     * in the current session, he will not be redirected to the external service anymore as HybridAuth keeps a session state
     * with authentication requests. Therefore we can call this method repeatedly after the first login (e.g. to fetch
     * the external profile) as it will return the data stored in the session instead of authenticating again.
     *
     * @return \CustomerManagementFrameworkBundle\Authentication\Sso\DefaultHybridAuthHandler
     */
    protected function authenticateHybridAuth()
    {
        /** @var \CustomerManagementFrameworkBundle\Authentication\Sso\DefaultHybridAuthHandler $hybridAuthHandler */
        $hybridAuthHandler = Pimcore::getDiContainer()->get('CustomerManagementFramework\Authentication\Sso\HybridAuthHandler');
        $hybridAuthHandler->authenticate($this->getRequest());

        return $hybridAuthHandler;
    }

    /**
     * The action we want to open after login
     *
     * /auth/secure
     */
    public function secureAction()
    {
        // here we'd redirect to a login page. for debugging purposes just throw an exception
        if (!$this->authService->isLoggedIn()) {
            throw new RuntimeException('Auth has no valid identity');
        }

        $this->view->customer           = $this->authService->getCustomer();
        $this->view->ssoIdentityService = Factory::getInstance()->getSsoIdentityService();
    }

    /**
     * @param array $errors
     * @param Zend_Form $form
     *
     * @return array
     */
    protected function addFormErrors(array $errors, Zend_Form $form)
    {
        foreach ($form->getErrors() as $fieldName => $fieldErrors) {
            foreach ($fieldErrors as $fieldError) {
                $errors[] = sprintf('%s:%s', $fieldName, $fieldError);
            }
        }

        return $errors;
    }

    /**
     * @return Zend_Form
     *
     * @throws Zend_Form_Exception
     */
    protected function buildLoginForm()
    {
        $email = new Zend_Form_Element_Text('email');
        $email
            ->setRequired(true)
            ->addValidators([
                new Zend_Validate_NotEmpty(),
                new Zend_Validate_EmailAddress()
            ]);

        $password = new Zend_Form_Element_Password('password');
        $password
            ->setRequired(true)
            ->addValidators([
                new Zend_Validate_NotEmpty()
            ]);

        $form = new Zend_Form();
        $form->addElements([$email, $password]);

        return $form;
    }
}
