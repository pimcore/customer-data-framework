<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginFormType;
use AppBundle\Form\RegisterFormType;
use AppBundle\Model\Customer;
use CustomerManagementFrameworkBundle\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception\DuplicateCustomerException;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use Pimcore\Controller\FrontendController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Website\Auth\Adapter\Customer as CustomerAuthAdapter;
use Website\Auth\AuthService;
use Website\Auth\RegistrationFormHandler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/auth")
 */
class AuthController extends FrontendController
{
    /**
     * @var AuthService
     */
    protected $authService;


    /**
     * Just an index page redirecting to login
     *
     * @param Request $request
     * @Route("/index")
     */
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl(
            'app_auth_login'
        ));
    }

    /**
     * @param Request $request
     * @Route("/login")
     */
    public function loginAction(Request $request, UserInterface $user = null)
    {
        // redirect to secure action if already logged in
        /*if ($user) {
            $redirect();
            return;
        }*/

        $customer = \Pimcore\Model\Object\Customer::getById(6692811);
        print 'class' . get_class($customer);

        $authenticationUtils = $this->get('security.authentication_utils');
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $formData =[
            '_username' => $lastUsername,
        ];

        $form = $this->createForm(LoginFormType::class, $formData, [
            'action' => $this->generateUrl('app_auth_login'),
        ]);

        $this->view->form = $form->createView();
        $this->view->error = $error;

    }

    /**
     * @Route("/logout")
     */
    public function logoutAction()
    {
        //logout is handled by security component, therefore nothing to do here
    }

    /**
     * Registration can either be called with a provider parameter or without. If a provider is passed, it will try
     * to read a SSO identity for the given provider from the HybridAuth store. This identity will then be added to the
     * customer profile and will pre-fill customer data (e.g. name if given).
     *
     * @Route("/register")
     */
    public function registerAction(Request $request)
    {
        $registrationFormHandler = new RegistrationFormHandler();

        // create a new, empty customer instance
        /** @var \CustomerManagementFrameworkBundle\Model\CustomerInterface|\Pimcore\Model\Object\Customer $customer */
        $customer = $this->container->get('cmf.customer_provider')
            ->create();

        /** @var SsoIdentityInterface $ssoIdentity */
        $ssoIdentity = null;

        // we come from an SSO provider - try to load the SSO identity
        if ($request->get('provider')) {
            // call authenticate again - if we're logged in the auth handler will keep auth result
            // in its session and not authenticate again
            $hybridAuthHandler = $this->authenticateHybridAuth($request);

            // try to load a customer with the given identity from our storage. if this succeeds, we can't register the customer
            // and should either log in the existing identity or show an error. for simplicity, we just throw an exception.
            if ($hybridAuthHandler->getCustomerFromAuthResponse($request)) {
                throw new \RuntimeException('Customer is already registered');
            }

            // update customer to be registered with auth response (SSO identity, profile data)
            /** @var \Pimcore\Model\Object\SsoIdentity $ssoIdentity */
            $ssoIdentity = $hybridAuthHandler->updateCustomerFromAuthResponse($customer, $request);
        }

        // build the registration form and pre-fill it with customer data
        $form = $this->createForm(RegisterFormType::class, [], [
            'action' => $this->generateUrl('app_auth_login'),
        ]);

        $form->handleRequest($request);

        $errors = [];
        
        if ($form->isSubmitted() && $form->isValid()) {

                $customer->setValues($form->getData());
                $customer->setActive(true);

                try {
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
                    //$this->authService->login($customer);
                    return $this->get('security.authentication.guard_handler')
                        ->authenticateUserAndHandleSuccess(
                            $customer,
                            $request,
                            null,
                            'main'
                        );
            } catch (DuplicateCustomerException $e) {
                $errors[] = "Customer already exists";
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $this->view->customer = $customer;
        $this->view->form     = $form->createView();
        $this->view->errors   = $errors;
    }

    /**
     * This actions starts the SSO login by passing a provider parameter which is passed to HybridAuth. If the given
     * provider is configured, HybridAuth will redirect to the given service login page.
     *
     * @param Request $request
     *
     * @route("/hybridauth")
     */
    public function hybridauthAction(Request $request, UserInterface $customer = null)
    {

        /** @var \CustomerManagementFrameworkBundle\Authentication\Sso\DefaultHybridAuthHandler $hybridAuthHandler */
        $hybridAuthHandler = null;

        try {
            $hybridAuthHandler = $this->authenticateHybridAuth($request);
        } catch (\CustomerManagementFrameworkBundle\Authentication\AuthenticationException $e) {
            \Pimcore\Logger::warning('Failed to log in via SSO: ' . $e->getMessage());

            return $this->redirectToRoute("app_auth_login");

        }

        // SSO login succeeded - try to get a local customer with the returned SSO identity
        $customer = $hybridAuthHandler->getCustomerFromAuthResponse($request);

        if ($customer) {
            // if there's a customer in the session and a customer is found from the SSO identity, abort if they don't match
            if ($customer) {
                if ($customer->getId() !== $this->authService->getCustomer()->getId()) {
                    throw new \RuntimeException('We have a logged in customer and found a customer from the SSO identity, but do not match');
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
            return $this->redirectToRoute("app_auth_secure");
        } else {
            return $this->redirectToRoute("app_auth_register");
        }
    }

    /**
     * Fetches the HybridAuth handler from the service container and starts authenticating. If the user already authenticated
     * in the current session, he will not be redirected to the external service anymore as HybridAuth keeps a session state
     * with authentication requests. Therefore we can call this method repeatedly after the first login (e.g. to fetch
     * the external profile) as it will return the data stored in the session instead of authenticating again.
     *
     * @param Request $request
     *
     * @return \CustomerManagementFrameworkBundle\Authentication\Sso\DefaultHybridAuthHandler
     */
    protected function authenticateHybridAuth(Request $request)
    {
        /** @var \CustomerManagementFrameworkBundle\Authentication\Sso\DefaultHybridAuthHandler $hybridAuthHandler */
        $hybridAuthHandler = $this->container->get('cmf.authentication.sso.hybrid_auth_handler');
        $hybridAuthHandler->authenticate($request);

        return $hybridAuthHandler;
    }

    /**
     * The action we want to open after login
     *
     * @param Request $request
     * @param UserInterface $user
     *
     * @Route("/secure")
     */
    public function secureAction(Request $request, UserInterface $user)
    {
        $this->view->customer           = $user;
        $this->view->ssoIdentityService = $this->container->get('cmf.authentication.sso.identity_service');
    }

    /**
     * @param array $errors
     * @param Zend_Form $form
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

}
