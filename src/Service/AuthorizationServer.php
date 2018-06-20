<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 11:29
 */

namespace CustomerManagementFrameworkBundle\Service;

use AppBundle\Model\Customer;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\AccessTokenRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\AuthCodeRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\ClientRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\RefreshTokenRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\ScopeRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\OAuth2\Server\Exception\OAuthServerException;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\User;
use Pimcore\Security\Encoder\Factory\UserAwareEncoderFactory;
use Pimcore\Security\Encoder\PasswordFieldEncoder;
use Pimcore\Tool\RestClient\Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;


class AuthorizationServer{

    static public $GRANT_TYPE_AUTH_CODE = "authorization_code";

    /*
     * @var string
     */
    private $currentGrantType = null;

    /*
     * @var AuthCodeRepository
     */
    private $authCodeRepository = null;

    /**
     * @var clientRepository
     */
    private $clientRepository = null;

    /**
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    private $server = null;

    /**
     * @var CustomerProviderInterface $customerProvider
     */
    private $currentUser = null;


    /**
     * @param string $grantType
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function validateClient(string $grantType, Request $request, $encryptionKey){

        if($grantType !== self::$GRANT_TYPE_AUTH_CODE){
            throw new Exception("AuthorizationServer ERROR: GRANT TYPE: ".$grantType." NOT SUPPORTED");
        }

        $this->currentGrantType = $grantType;

        switch ($this->currentGrantType){
            case self::$GRANT_TYPE_AUTH_CODE:
                return $this->getAuthToken($request, $encryptionKey);
        }

    }

    public function getAccessTokenForAuthGrantClient(Request $request, string $encryptionKey){

        try {

            $this->initAuthGrantServer($encryptionKey);

            $psr7Factory = new DiactorosFactory();
            $psrRequest = $psr7Factory->createRequest($request);

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            $httpFoundationFactory = new HttpFoundationFactory();
            $symfonyResponse = $httpFoundationFactory->createResponse($this->server->respondToAccessTokenRequest($psrRequest,$psrResponse));

            return $symfonyResponse;

        } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

            if($exception->getHttpStatusCode()==Response::HTTP_UNAUTHORIZED){
                throw new HttpException(401, "AUTHORIZATION FAILED");
            }
            else throw new $exception;


        } catch (\Exception $exception) {
            $this->handleErrorException($exception);
        }


    }

    public function authUser(string $username, string $password)
    {
        $customerProvider = \Pimcore::getContainer()->get(CustomerProviderInterface::class);
        $this->currentUser = $customerProvider->getActiveCustomerByEmail($username);

        if(!$this->currentUser){
            throw new HttpException(401, "AUTHORIZATION FAILED");
        }

        /**
         * @var DataObject\ClassDefinition\Data\Password $field
         */
        $passwordField = $this->currentUser->getClass()->getFieldDefinition('password');

        if(!$passwordField->verifyPassword($password, $this->currentUser)){
            throw new HttpException(403, "AUTH FAILED");
        }
    }

    private function initAuthGrantServer($encryptionKey){

        $this->clientRepository = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Repository\Service\Auth\ClientRepository");
        $scopeRepository = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Repository\Service\Auth\ScopeRepository");
        $accessTokenRepository = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Repository\Service\Auth\AccessTokenRepository");
        $this->authCodeRepository = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Repository\Service\Auth\AuthCodeRepository");
        $refreshTokenRepository = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Repository\Service\Auth\RefreshTokenRepository");

        $privateKey = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

        if(!key_exists("private_key_dir", $privateKey)){
            throw new Exception("AuthorizationServer ERROR: pimcore_customer_management_framework.oauth_server.private_key_dir NOT DEFINED IN config.xml");
        }
        $privateKey = $privateKey["private_key_dir"];

        $encryptionKey = "djaisdj233ikodkaspo3434hgfgdfgf568kfsd34dfsdskdpo";//base64_encode(random_bytes(32));

        /**
         * @var \League\OAuth2\Server\AuthorizationServer $server
         */
        $server = new \League\OAuth2\Server\AuthorizationServer(
            $this->clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptionKey
        );

        $grant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
            $this->authCodeRepository,
            $refreshTokenRepository,
            new \DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );

        $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        $server->enableGrantType(
            $grant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        $this->server = $server;
    }

    private function getAuthToken(Request $request, string $encryptionKey){

        $this->initAuthGrantServer($encryptionKey);

        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($this->currentUser); // an instance of UserEntityInterface

            $this->authCodeRepository->setUserIdenifier($this->currentUser->getIdentifier());
            $this->authCodeRepository->setEncryptionKey($encryptionKey);

            //$request->request->set("client_secret", $encoder->encodePassword($plainPassword,$userModel->getSalt()));

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            $response = $this->server->completeAuthorizationRequest($authRequest, $psrResponse);

            $redirectResponse = new RedirectResponse($request->query->get("redirect_url"));
            $redirectResponse->headers->add($response->getHeaders());

            return $redirectResponse;

        } catch (OAuthServerException $exception) {

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($psrResponse);

        } catch (\Exception $exception) {
            $this->handleErrorException($exception);
        }

    }

    /**
     * @param \Exception $exception
     * @return Response
     * @throws \Exception
     */
    private function handleErrorException(\Exception $exception){
        if(\Pimcore::inDebugMode()) {
            throw $exception;
        }

        $symfonyResponse = new Response();
        $symfonyResponse->setStatusCode(500)->setContent("error happened");

        return $symfonyResponse;
    }
}