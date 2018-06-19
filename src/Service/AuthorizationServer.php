<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 11:29
 */

namespace CustomerManagementFrameworkBundle\Service;

use AppBundle\Model\Customer;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\Repository\AccessTokenRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\Repository\AuthCodeRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\Repository\ClientRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\Repository\RefreshTokenRepository;
use CustomerManagementFrameworkBundle\Repository\Service\Auth\Repository\ScopeRepository;
use CustomerManagementFrameworkBundle\Service\Auth\Entities\ServerRequest;
use CustomerManagementFrameworkBundle\Service\Auth\Entities\UserEntity;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\OAuth2\Server\Exception\OAuthServerException;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Tool\RestClient\Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationServer{

    static public $GRANT_TYPE_AUTH_CODE = "authorization_code";

    /*
     * @var string
     */
    private $currentGrantType = null;

    /*
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    private $server = null;

    public function validateClient(string $grantType, Request $request){

        if($grantType !== self::$GRANT_TYPE_AUTH_CODE){
            throw new Exception("AuthorizationServer ERROR: GRANT TYPE: ".$grantType." NOT SUPPORTED");
        }

        $this->currentGrantType = $grantType;

        switch ($this->currentGrantType){
            case self::$GRANT_TYPE_AUTH_CODE:
                return $this->startAuthGrant($request);
        }

    }

    public function getAccessTokenForClient(Request $request){

        try {

            $psr7Factory = new DiactorosFactory();
            $psrRequest = $psr7Factory->createRequest($request);

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            $httpFoundationFactory = new HttpFoundationFactory();
            $symfonyResponse = $httpFoundationFactory->createResponse($this->server->respondToAccessTokenRequest($psrRequest,$psrResponse));

            return $symfonyResponse;

        } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

            $httpFoundationFactory = new HttpFoundationFactory();
            $symfonyResponse = $httpFoundationFactory->createResponse($exception->generateHttpResponse($psrResponse));

            return $symfonyResponse;

        } catch (\Exception $exception) {
            $this->handleErrorException($exception);
        }


    }

    private function initServer(){

        $clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
        $authCodeRepository = new AuthCodeRepository(); // instance of AuthCodeRepositoryInterface
        $refreshTokenRepository = new RefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface

        $privateKey = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

        if(!key_exists("private_key_dir", $privateKey)){
            throw new Exception("AuthorizationServer ERROR: pimcore_customer_management_framework.oauth_server.private_key_dir NOT DEFINED IN config.xml");
        }
        $privateKey = $privateKey["private_key_dir"];

        $encryptionKey = base64_encode(random_bytes(32));//"djaisdj233ikodkaspo3434hgfgdfgf568kfsd34dfsdskdpo";

        /*
         * @var \League\OAuth2\Server\AuthorizationServer $server
         */
        $server = new \League\OAuth2\Server\AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptionKey
        );

        $grant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            new \DateInterval('PT1M') // authorization codes will expire after 1 minutes
        );

        $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        $server->enableGrantType(
            $grant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        $this->server = $server;
    }

    private function startAuthGrant(Request $request){

        $this->initServer();

        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);

        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.

            $userClassModel = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

            if(!key_exists("user_class_model", $userClassModel)){
                throw new Exception("AuthorizationServer ERROR: pimcore_customer_management_framework.oauth_server.user_class_model NOT DEFINED IN config.xml");
            }
            $userClassModel = $userClassModel["user_class_model"];
            $userModel = null;
            eval('$userModel=' . $userClassModel.'::getByEmail("'.$request->request->get("client_email").'")->current();');

            //$userModel = Customer::getByEmail($request->request->get("client_email"))->current();

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($userModel); // an instance of UserEntityInterface

            //$request->request->set("client_secret", $userModel->getPassword());

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            return $this->server->completeAuthorizationRequest($authRequest, $psrResponse);

        } catch (OAuthServerException $exception) {

            $symfonyResponse = new Response();
            $psrResponse = $psr7Factory->createResponse($symfonyResponse);

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($psrResponse);

        } catch (\Exception $exception) {
            $this->handleErrorException($exception);
        }

    }

    private function handleErrorException(\Exception $exception){
        if(\Pimcore::inDebugMode()) {
            throw $exception;
        }

        $symfonyResponse = new Response();
        $symfonyResponse->setStatusCode(500)->setContent("error happened");

        return $symfonyResponse;
    }

}