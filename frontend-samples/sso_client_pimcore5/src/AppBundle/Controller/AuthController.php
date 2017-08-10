<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginFormType;
use AppBundle\Form\RegistrationFormHandler;
use AppBundle\Form\RegistrationFormType;
use CustomerManagementFrameworkBundle\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception\DuplicateCustomerException;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use CustomerManagementFrameworkBundle\Security\Authentication\LoginManagerInterface;
use CustomerManagementFrameworkBundle\Security\OAuth\OAuthHandler;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Pimcore\Controller\FrontendController;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/auth")
 */
class AuthController extends FrontendController
{
    /**
     * Just an index page redirecting to login
     *
     * @Route("/")
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return $this->redirectToRoute('app_auth_login');
    }

    /**
     * The action we want to open after login. The Security annotation defines that the action needs a valid user
     * to be accessible.
     *
     * @Route("/secure")
     * @Security("has_role('ROLE_USER')")
     */
    public function secureAction()
    {
    }

    /**
     * @Route("/login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @param UserInterface|null $user
     *
     * @return Response|null
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils, OAuthHandler $oAuthHandler, UserInterface $user = null)
    {
        // redirect to secure action if already logged in
        if ($user) {
            return $this->redirectToRoute('app_auth_secure');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // if error is an AccountNotLinkedException save the error containing the OAuth response
        // to the session and redirect to registration
        if ($error instanceof AccountNotLinkedException) {
            $oAuthKey = Uuid::uuid4();
            $oAuthHandler->saveOAuthErrorToSession($request, $oAuthKey, $error);

            return $this->redirectToRoute('app_auth_register', [
                'oAuthKey' => $oAuthKey
            ]);
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $formData = [
            '_username' => $lastUsername,
        ];

        $form = $this->createForm(LoginFormType::class, $formData, [
            'action' => $this->generateUrl('app_auth_login'),
        ]);

        $this->view->form  = $form->createView();
        $this->view->error = $error;
    }

    /**
     * @Route("/register/{oAuthKey}", defaults={"oAuthKey" = null})
     *
     * Registration can either be called with a provider parameter or without. If a provider is passed, it will try
     * to read a SSO identity for the given provider from the HybridAuth store. This identity will then be added to the
     * customer profile and will pre-fill customer data (e.g. name if given).
     *
     * @param Request $request
     * @param CustomerProviderInterface $customerProvider
     * @param LoginManagerInterface $loginManager
     * @param OAuthHandler $oAuthHandler
     * @param SsoIdentityServiceInterface $ssoIdentityService
     * @param RegistrationFormHandler $registrationFormHandler
     * @param string|null $oAuthKey
     * @param UserInterface|null $user
     *
     * @return Response|null
     */
    public function registerAction(
        Request $request,
        CustomerProviderInterface $customerProvider,
        LoginManagerInterface $loginManager,
        OAuthHandler $oAuthHandler,
        SsoIdentityServiceInterface $ssoIdentityService,
        RegistrationFormHandler $registrationFormHandler,
        string $oAuthKey = null,
        UserInterface $user = null
    )
    {
        // redirect to login action if already logged in - login will take care of redirecting to
        // target path
        if ($user) {
            return $this->redirectToRoute('app_auth_login');
        }

        // create a new, empty customer instance
        /** @var CustomerInterface|\Pimcore\Model\Object\Customer $customer */
        $customer = $customerProvider->create();

        /** @var AccountNotLinkedException $oAuthError */
        $oAuthError = null;

        /** @var \Pimcore\Model\Object\SsoIdentity|SsoIdentityInterface $ssoIdentity */
        $ssoIdentity = null;

        // we handled an oAuth authorization - create SSO Identity and apply profile data to customer object
        if (null !== $oAuthKey) {
            // load OAuth error containing auth response from session (saved on login)
            $oAuthError = $oAuthHandler->loadOAuthErrorFromSession($request, $oAuthKey);

            if ($oAuthError) {
                // fetch user information from provider
                $userInformation = $oAuthHandler->getUserInformation(
                    $request,
                    $oAuthError->getResourceOwnerName(),
                    $oAuthError->getRawToken()
                );

                if ($userInformation) {
                    // try to load a customer with the given identity from our storage. if this succeeds, we can't register
                    // the customer and should either log in the existing identity or show an error. for simplicity, we just
                    // throw an exception here.
                    if ($oAuthHandler->getCustomerFromAuthResponse($userInformation)) {
                        throw new \RuntimeException('Customer is already registered');
                    }

                    // update customer to be registered with auth response (SSO identity, profile data)
                    $ssoIdentity = $oAuthHandler->updateUserFromUserInformation($customer, $userInformation);
                }
            }
        }

        $formData = $registrationFormHandler->buildFormData($customer);

        // build the registration form and pre-fill it with customer data
        $form = $this->createForm(RegistrationFormType::class, $formData, [
            'action' => $this->generateUrl('app_auth_register', [
                'oAuthKey' => $oAuthKey
            ]),
        ]);

        $form->handleRequest($request);

        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $registrationFormHandler->updateCustomerFromForm($customer, $form);
            $customer->setActive(true);

            try {
                $customer->save();

                // add SSO identity to customer object
                if (null !== $ssoIdentity) {
                    $ssoIdentityService->addSsoIdentity($customer, $ssoIdentity);
                    $ssoIdentity->save();
                    $customer->save();
                }

                $response = $this->redirectToRoute('app_auth_secure');

                // log user in manually
                // pass response to login manager as it adds potential remember me cookies
                $loginManager->login($customer, $request, $response);

                return $response;
            } catch (DuplicateCustomerException $e) {
                $errors[] = "Customer already exists";
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        // re-save user info to session as we need it in subsequent requests (e.g. after form errors) or
        // when form is rendered for the first time
        if (null !== $oAuthKey && null !== $oAuthError) {
            $oAuthHandler->saveOAuthErrorToSession($request, $oAuthKey, $oAuthError);
        }

        $this->view->customer = $customer;
        $this->view->form     = $form->createView();
        $this->view->errors   = $errors;
    }
}
