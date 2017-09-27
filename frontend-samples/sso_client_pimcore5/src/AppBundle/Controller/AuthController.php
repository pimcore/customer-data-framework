<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace AppBundle\Controller;

use AppBundle\Form\LoginFormType;
use AppBundle\Form\RegistrationFormHandler;
use AppBundle\Form\RegistrationFormType;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception\DuplicateCustomerException;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Security\Authentication\LoginManagerInterface;
use CustomerManagementFrameworkBundle\Security\OAuth\Exception\AccountNotLinkedException;
use CustomerManagementFrameworkBundle\Security\OAuth\OAuthRegistrationHandler;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Pimcore\Controller\FrontendController;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/auth")
 */
class AuthController extends FrontendController
{
    /**
     * @Route("/", name="app_auth_index")
     *
     * @param UserInterface|null $user
     *
     * @return RedirectResponse
     */
    public function indexAction(Request $request, UserInterface $user = null)
    {
        return $this->buildUserRedirect($user);
    }

    /**
     * @Route("/login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @param OAuthRegistrationHandler $oAuthHandler
     * @param UserInterface|null $user
     *
     * @return null|Response
     */
    public function loginAction(
        AuthenticationUtils $authenticationUtils,
        OAuthRegistrationHandler $oAuthHandler,
        UserInterface $user = null
    ) {
        // redirect to index action if already logged in
        if ($user && $this->isGranted('ROLE_USER')) {
            return $this->buildUserRedirect($user);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // OAuth handling - the OAuth authenticator is configured to return to the login page on errors
        // (see failure_path configuration) - therefore we can fetch the last authentication error
        // here. If the error is an AccountNotLinkedException (as thrown by our user provider) save the
        // OAuth token to the session and redirect to registration with a special key which can be used
        // to load the token to prepopulate the registration form with account data.
        if ($error instanceof AccountNotLinkedException) {
            // this can be anything - for simplicity we just use an UUID as it is unique and random
            $registrationKey = Uuid::uuid4();
            $oAuthHandler->saveToken($registrationKey, $error->getToken());

            return $this->redirectToRoute('app_auth_register', [
                'registrationKey' => $registrationKey
            ]);
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $formData = [
            '_username' => $lastUsername,
        ];

        $form = $this->createForm(LoginFormType::class, $formData);

        $this->view->form  = $form->createView();
        $this->view->error = $error;
    }

    /**
     * If registration is called with a registration key, the key will be used to look for an existing OAuth token in
     * the session. This OAuth token will be used to fetch user info which can be used to prepopulate the form and to
     * link a SSO identity to the created customer object.
     *
     * This could be further separated into services, but was kept as single method for demonstration purposes as the
     * registration process is different on every project.
     *
     * @Route("/register/{registrationKey}", defaults={"registrationKey" = null})
     *
     * @param Request $request
     * @param CustomerProviderInterface $customerProvider
     * @param LoginManagerInterface $loginManager
     * @param OAuthRegistrationHandler $oAuthHandler
     * @param RegistrationFormHandler $registrationFormHandler
     * @param string|null $registrationKey
     * @param UserInterface|null $user
     *
     * @return Response|null
     */
    public function registerAction(
        Request $request,
        CustomerProviderInterface $customerProvider,
        OAuthRegistrationHandler $oAuthHandler,
        LoginManagerInterface $loginManager,
        RegistrationFormHandler $registrationFormHandler,
        string $registrationKey = null,
        UserInterface $user = null
    ) {
        // redirect to secure page if we have a user
        if ($user) {
            return $this->buildUserRedirect($user);
        }

        // create a new, empty customer instance
        /** @var CustomerInterface|\Pimcore\Model\DataObject\Customer $customer */
        $customer = $customerProvider->create();

        /** @var OAuthToken $oAuthToken */
        $oAuthToken = null;

        /** @var UserResponseInterface $oAuthUserInfo */
        $oAuthUserInfo = null;

        // load previously stored token from the session and try to load user profile
        // from provider
        if (null !== $registrationKey) {
            $oAuthToken    = $oAuthHandler->loadToken($registrationKey);
            $oAuthUserInfo = $oAuthHandler->loadUserInformation($oAuthToken);
        }

        if (null !== $oAuthUserInfo) {
            // try to load a customer with the given identity from our storage. if this succeeds, we can't register
            // the customer and should either log in the existing identity or show an error. for simplicity, we just
            // throw an exception here.
            // this shouldn't happen as the login would log in the user if found
            if ($oAuthHandler->getCustomerFromUserResponse($oAuthUserInfo)) {
                throw new \RuntimeException('Customer is already registered');
            }
        }

        // the registration form handler is just a utility class to map pimcore object data to form
        // and vice versa. TODO: is there a better way (as Symfony does with Doctrine entities)?
        $formData = $registrationFormHandler->buildFormData($customer);
        if (null !== $oAuthToken) {
            $formData = $this->mergeOAuthFormData($formData, $oAuthUserInfo);
        }

        // build the registration form and pre-fill it with customer data
        $form = $this->createForm(RegistrationFormType::class, $formData);
        $form->handleRequest($request);

        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $registrationFormHandler->updateCustomerFromForm($customer, $form);
            $customer->setActive(true);

            try {
                $customer->save();

                // add SSO identity from OAuth data
                if (null !== $oAuthUserInfo) {
                    $oAuthHandler->connectSsoIdentity($customer, $oAuthUserInfo);
                }

                $response = $this->buildUserRedirect($customer);

                // log user in manually
                // pass response to login manager as it adds potential remember me cookies
                $loginManager->login($customer, $request, $response);

                return $response;
            } catch (DuplicateCustomerException $e) {
                $errors[] = 'Customer already exists';
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        // re-save user info to session as we need it in subsequent requests (e.g. after form errors) or
        // when form is rendered for the first time
        if (null !== $registrationKey && null !== $oAuthToken) {
            $oAuthHandler->saveToken($registrationKey, $oAuthToken);
        }

        $this->view->customer = $customer;
        $this->view->form     = $form->createView();
        $this->view->errors   = $errors;
    }

    /**
     * Connects an already logged in user to an auth provider
     *
     * @Route("/oauth/connect/{service}", name="app_auth_oauth_connect")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @param OAuthRegistrationHandler $oAuthHandler
     * @param UserInterface $user
     * @param string $service
     *
     * @return RedirectResponse
     */
    public function oAuthConnectAction(
        Request $request,
        OAuthRegistrationHandler $oAuthHandler,
        UserInterface $user,
        string $service
    ) {
        $resourceOwner = $oAuthHandler->getResourceOwner($service);

        $redirectUrl = $this->generateUrl('app_auth_oauth_connect', [
            'service' => $service
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // redirect to authorization
        if (!$resourceOwner->handles($request)) {
            $authorizationUrl = $oAuthHandler->getAuthorizationUrl($request, $service, $redirectUrl);

            return $this->redirect($authorizationUrl);
        }

        // get access token from URL
        $accessToken = $resourceOwner->getAccessToken($request, $redirectUrl);

        // e.g. user cancelled auth on provider side
        if (null === $accessToken) {
            return $this->buildUserRedirect($user);
        }

        $oAuthUserInfo = $resourceOwner->getUserInformation($accessToken);

        // we don't want to allow linking an OAuth account to multiple customers
        if ($oAuthHandler->getCustomerFromUserResponse($oAuthUserInfo)) {
            throw new \RuntimeException('There\'s already a customer registered with this provider identity');
        }

        // create a SSO identity object and save it to the user
        $oAuthHandler->connectSsoIdentity($user, $oAuthUserInfo);

        // redirect to secure page which should now list the newly linked profile
        return $this->buildUserRedirect($user);
    }

    private function buildUserRedirect(UserInterface $user = null): RedirectResponse
    {
        if ($user && $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_auth_login');
    }

    private function mergeOAuthFormData(
        array $formData,
        UserResponseInterface $userInformation
    ): array {
        return array_replace([
            'firstname' => $userInformation->getFirstName(),
            'lastname'  => $userInformation->getLastName(),
            'email'     => $userInformation->getEmail()
        ], $formData);
    }
}
