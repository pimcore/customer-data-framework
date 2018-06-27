<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 11:29
 */

namespace CustomerManagementFrameworkBundle\Service;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\AccessTokenRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\AuthCodeRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\ClientRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\RefreshTokenRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\ScopeRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ImplicitGrant;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Http\Message\ServerRequestInterface;


class AuthorizationServer{

    const GRANT_TYPE_AUTH_GRANT = 'auth_grant';
    const GRANT_TYPE_IMPLICIT_GRANT = 'implicit_grant';
    const GRANT_TYPE_PASSWORD_GRANT = 'password';

    const GRANT_TYPES = [
        self::GRANT_TYPE_AUTH_GRANT,
        self::GRANT_TYPE_IMPLICIT_GRANT,
        self::GRANT_TYPE_PASSWORD_GRANT
    ];

    /**
     * @var AuthCodeRepository
     */
    private $authCodeRepository = null;

    /**
     * @var AuthCodeRepository
     */
    private $accessTokenRepository = null;

    /**
     * @var clientRepository
     */
    private $clientRepository = null;

    /**
     * @var RefreshTokenRepository
     */
    private $refreshTokenRepository = null;

    /**
     * @var ScopeRepository
     */
    private $scopeRepository = null;

    /**
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    private $server = null;

    /**
     * @var CustomerProviderInterface $customerProvider
     */
    private $currentUser = null;

    public function __construct(ClientRepository $clientRepository, ScopeRepository $scopeRepository, AccessTokenRepository $accessTokenRepository, AuthCodeRepository $authCodeRepository, RefreshTokenRepository $refreshTokenRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->scopeRepository = $scopeRepository;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->authCodeRepository = $authCodeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    /**
     * @param string $grantType
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @throws \Exception
     */
    public function validateClient(string $grantType, Request $request){

        if(!in_array($grantType, self::GRANT_TYPES)){
            throw new \Exception("GRANT TYPE: ".$grantType." NOT SUPPORTED", 400);
        }

        try {
            switch ($grantType) {
                case self::GRANT_TYPE_AUTH_GRANT:
                    return $this->getAuthTokenForAuthGrantClient($request);
                case self::GRANT_TYPE_IMPLICIT_GRANT:
                    return $this->getAccessTokenForImplicitGrantClient($request);
                case self::GRANT_TYPE_PASSWORD_GRANT:
                    return $this->getAccessTokenForPasswordGrantClient($request);
            }
        }
        catch (\Exception $error){
            throw new \Exception("AuthorizationServer ERROR: ".$error->getMessage(), $error->getCode());
        }

    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function getAccessTokenForAuthGrantClient(Request $request){

        try {

            $this->initAuthGrantServer();

            $psr7Info = $this->getPsr7RequestAndResponse($request);

            $httpFoundationFactory = new HttpFoundationFactory();
            $symfonyResponse = $httpFoundationFactory->createResponse($this->server->respondToAccessTokenRequest($psr7Info["request"],$psr7Info["response"]));

            return $symfonyResponse;

        } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
            throw new \Exception($exception->getMessage(),500);

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }


    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function getRefreshTokenForAuthGrantClient(Request $request){

        try {

            $this->initRefreshGrantServer();

            $psr7Info = $this->getPsr7RequestAndResponse($request);

            $httpFoundationFactory = new HttpFoundationFactory();
            $symfonyResponse = $httpFoundationFactory->createResponse($this->server->respondToAccessTokenRequest($psr7Info["request"],$psr7Info["response"]));

            return $symfonyResponse;

        } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

            throw new \Exception($exception->getMessage(),500);

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }

    }

    /**
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @throws \Exception
     */
    public function getAccessTokenForImplicitGrantClient(Request $request){

        try {

            return $this->getAuthTokenForImplicitGrantClient($request);

        } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

            throw new \Exception($exception->getMessage(),500);

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }


    }

    /**
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @throws \Exception
     */
    public function getAccessTokenForPasswordGrantClient(Request $request){

        try {

            return $this->getAuthTokenForPasswordGrantClient($request);

        } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

            throw new \Exception($exception->getMessage(),500);

        } catch (\Exception $exception) {

            throw new \Exception($exception->getMessage(), $exception->getCode());
        }


    }

    /**
     * @param Request $request
     * @return JsonResponse|ServerRequestInterface
     * @throws \Exception
     */
    public function validateAuthenticatedRequest(Request $request){

        /**
         * @var \CustomerManagementFrameworkBundle\Repository\Service\Auth\AccessTokenRepository $accessTokenRepository
         */
        $accessTokenRepository = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Repository\Service\Auth\AccessTokenRepository");

        $oauthServerConfig = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

        if(!key_exists("publicKeyDir", $oauthServerConfig)){
            throw new \Exception("pimcore_customer_management_framework.oauth_server.publicKeyDir NOT DEFINED IN config.xml", 400);
        }
        $publicKeyPath = $oauthServerConfig["publicKeyDir"];

        $server = new \League\OAuth2\Server\ResourceServer(
            $accessTokenRepository,
            $publicKeyPath
        );

        new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server);

        try{
            $psr7Factory = new DiactorosFactory();
            $psrRequest = $psr7Factory->createRequest($request);

            return $server->validateAuthenticatedRequest($psrRequest);
        }
        catch (OAuthServerException $exception){
            throw new \Exception($exception->getMessage(), 400);
        }

    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     * @throws \Exception
     */
    public function authUser(string $username, string $password)
    {
        $customerProvider = \Pimcore::getContainer()->get(CustomerProviderInterface::class);
        $this->currentUser = $customerProvider->getActiveCustomerByEmail($username);

        if(!$this->currentUser){
            throw new \Exception("User authentication failed", 401);
        }

        /**
         * @var DataObject\ClassDefinition\Data\Password $field
         */
        $passwordField = $this->currentUser->getClass()->getFieldDefinition('password');

        if(!$passwordField->verifyPassword($password, $this->currentUser)){
            throw new \Exception("User authentication failed", 401);
        }
    }

    /**
     * @throws \Exception
     */
    private function initAuthGrantServer(){

        $config = $this->handleConfigOptions();

        /**
         * @var \League\OAuth2\Server\AuthorizationServer $server
         */
        $server = new \League\OAuth2\Server\AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $config['privateKeyDir'],
            $config['encryptionKey']
        );

        $grant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new \DateInterval($config['expireAuthorizationCode'])
        );

        $grant->setRefreshTokenTTL(new \DateInterval($config['expireRefreshTokenCode'])); // refresh tokens will expire after 1 month

        $server->enableGrantType(
            $grant,
            new \DateInterval($config['expireAccessTokenCode']) // access tokens will expire after 1 hour
        );

        $this->server = $server;
    }

    /**
     * @throws \Exception
     */
    private function initRefreshGrantServer(){

        $config = $this->handleConfigOptions(false);

        /**
         * @var \League\OAuth2\Server\AuthorizationServer $server
         */
        $server = new \League\OAuth2\Server\AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $config['privateKeyDir'],
            $config['encryptionKey']
        );

        $grant = new \League\OAuth2\Server\Grant\RefreshTokenGrant($this->refreshTokenRepository);
        $grant->setRefreshTokenTTL(new \DateInterval($config['expireRefreshTokenCode']));

        $server->enableGrantType(
            $grant,
            new \DateInterval($config['expireAccessTokenCode'])
        );

        $this->server = $server;
    }

    /**
     * @throws \Exception
     */
    private function initImplicitGrantServer(){

        $config = $this->handleConfigOptions(false, false);

        /**
         * @var \League\OAuth2\Server\AuthorizationServer $server
         */
        $server = new \League\OAuth2\Server\AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $config['privateKeyDir'],
            $config['encryptionKey']
        );

        $server->enableGrantType(
            new ImplicitGrant(new \DateInterval($config['expireAccessTokenCode']), "?"),
            new \DateInterval($config['expireAccessTokenCode'])
        );

        $this->server = $server;
    }

    /**
     * @throws \Exception
     */
    private function initPasswordGrantServer(){

        $config = $this->handleConfigOptions(false, true);

        /**
         * @var \League\OAuth2\Server\AuthorizationServer $server
         */
        $server = new \League\OAuth2\Server\AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $config['privateKeyDir'],
            $config['encryptionKey']
        );

        $grant = new \League\OAuth2\Server\Grant\PasswordGrant(
            $this->userRepository,
            $this->refreshTokenRepository
        );

        $grant->setRefreshTokenTTL(new \DateInterval($config['expireRefreshTokenCode'])); // refresh tokens will expire after 1 month

        $server->enableGrantType(
            $grant,
            new \DateInterval($config['expireAccessTokenCode'])
        );

        $this->server = $server;
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws \Exception
     */
    private function getAuthTokenForAuthGrantClient(Request $request){

        $this->initAuthGrantServer();

        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($this->currentUser); // an instance of UserEntityInterface

            $this->authCodeRepository->setUserIdenifier($this->currentUser->getIdentifier());

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            $response = $this->server->completeAuthorizationRequest($authRequest, $psrResponse);

            $redirectResponse = new RedirectResponse($request->query->get("redirect_uri"));
            $redirectResponse->headers->add($response->getHeaders());

            return $redirectResponse;

        } catch (OAuthServerException $exception) {
            throw new \Exception($exception->getMessage(), 400);
        } catch (\Exception $exception) {
            throw new \Exception("getAuthTokenForAuthGrantClient: ".$exception->getMessage(), 500);
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws \Exception
     */
    private function getAuthTokenForImplicitGrantClient(Request $request){

        $this->initImplicitGrantServer();

        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($this->currentUser); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            $response = $this->server->completeAuthorizationRequest($authRequest, $psrResponse);

            $redirectResponse = new RedirectResponse($request->query->get("redirect_uri"));
            $redirectResponse->headers->add($response->getHeaders());

            return $redirectResponse;

        } catch (OAuthServerException $exception) {
            throw new \Exception($exception->getMessage(), 400);
        } catch (\Exception $exception) {
            throw new \Exception("getAuthTokenForImplicitGrantClient: ".$exception->getMessage(), 500);
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \Exception
     */
    private function getAuthTokenForPasswordGrantClient(Request $request){

        try {

            $this->initPasswordGrantServer();

            /**
             * @var \CustomerManagementFrameworkBundle\Repository\Service\Auth\AccessTokenRepository $accessTokenRepository
             */
            $this->accessTokenRepository->setUserIdenifier($this->currentUser->getId()); // an instance of UserEntityInterface

            $psr7Info = $this->getPsr7RequestAndResponse($request);

            $httpFoundationFactory = new HttpFoundationFactory();
            $symfonyResponse = $httpFoundationFactory->createResponse($this->server->respondToAccessTokenRequest($psr7Info["request"],$psr7Info["response"]));

            return $symfonyResponse;

        } catch (OAuthServerException $exception) {
            throw new \Exception($exception->getMessage(), 400);
        } catch (\Exception $exception) {
            throw new \Exception("getAuthTokenForPasswordGrantClient: ".$exception->getMessage(), 500);
        }

    }

    private function getPsr7RequestAndResponse(Request $request){
        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        $symfonyResponse = new Response();
        $psrResponse = $psr7Factory->createResponse($symfonyResponse);
        return [
            "request" => $psrRequest,
            "response" => $psrResponse
        ];
    }

    private function handleConfigOptions(bool $checkAuthCode=true, bool $checkRefreshToken=true){
        $oauthServerConfig = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

        $config = [];

        if(!key_exists("privateKeyDir", $oauthServerConfig)){
            throw new \Exception("pimcore_customer_management_framework.oauth_server.privateKeyDir NOT DEFINED IN config.xml", 400);
        }
        $config["privateKeyDir"] = $oauthServerConfig["privateKeyDir"];

        if(!key_exists("encryptionKey", $oauthServerConfig)){
            throw new \Exception("pimcore_customer_management_framework.oauth_server.encryptionKey NOT DEFINED IN config.xml", 400);
        }
        $config["encryptionKey"] = $oauthServerConfig["encryptionKey"];

        if($checkAuthCode) {
            if (!key_exists("expireAuthorizationCode", $oauthServerConfig)) {
                throw new \Exception("pimcore_customer_management_framework.oauth_server.expireAuthorizationCode NOT DEFINED IN config.xml", 400);
            }
            $config["expireAuthorizationCode"] = $oauthServerConfig["expireAuthorizationCode"];
        }

        if(!key_exists("expireAccessTokenCode", $oauthServerConfig)){
            throw new \Exception("pimcore_customer_management_framework.oauth_server.expireAccessTokenCode NOT DEFINED IN config.xml", 400);
        }
        $config["expireAccessTokenCode"] = $oauthServerConfig["expireAccessTokenCode"];

        if($checkRefreshToken) {
            if (!key_exists("expireRefreshTokenCode", $oauthServerConfig)) {
                throw new \Exception("pimcore_customer_management_framework.oauth_server.expireRefreshTokenCode NOT DEFINED IN config.xml", 400);
            }
            $config["expireRefreshTokenCode"] = $oauthServerConfig["expireRefreshTokenCode"];
        }

        return $config;
    }
}