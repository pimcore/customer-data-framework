<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 10:57
 */

namespace CustomerManagementFrameworkBundle\Controller\OAuth;

use AppBundle\Model\Customer;
use CustomerManagementFrameworkBundle\Entity\Service\Auth\AuthCode;
use CustomerManagementFrameworkBundle\Form\AuthType;
use CustomerManagementFrameworkBundle\Service\AuthorizationServer;
use Pimcore\Controller\FrontendController;
use Pimcore\Tool\Console;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ServerController extends FrontendController
{

    /**
     * @param Request $request
     * @Route("/form_auth_code", name="form_auth_code_path")
     * @return RedirectResponse
     * @throws \Pimcore\Tool\RestClient\Exception
     */
    public function formAuthorizeClient(Request $request)
    {

        $form = $this->createForm(AuthType::class);
        $form->handleRequest($request);

        $clientId = $request->query->get("client_id");
        $clientSecret = $request->query->get("client_secret");
        $redirectUrl = $request->query->get("redirect_url");
        $responseType = $request->query->get("response_type");

        $state = $request->query->get("state");
        $scope = $request->query->get("scope");

        if(!$clientId){
            throw new HttpException(400, "GET-PARAM: client_id is missing");
        }

        if(!$clientSecret){
            throw new HttpException(400, "GET-PARAM: client_secret is missing");
        }

        if(!$redirectUrl){
            throw new HttpException(400, "GET-PARAM: redirect_url is missing");
        }

        if(!$responseType){
            throw new HttpException(400, "GET-PARAM: response_type is missing");
        }

        if($form->isSubmitted() && $form->isValid()){

            /**
             * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
             */
            $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

            $authServerService->authUser($request->request->get("username"), $request->request->get("password"));

            $request->request->set("client_id", $request->query->get("client_id"));
            $request->request->set("response_type", $responseType);
            $request->request->set("scope", "basic");
            $request->request->set("redirect_uri", $redirectUrl);

            if($state){
                $request->request->set("state", $state);
            }
            if($scope){
                $request->request->set("scope", $scope);
            }

            $authInfo = $authServerService->validateClient(AuthorizationServer::$GRANT_TYPE_AUTH_CODE, $request);

            return $this->redirect(
                $this->generateUrl("access_token_path") .
                "?code=" . $authInfo["code"] .
                "&state=" . $authInfo["state"] .
                "&grant_type=authorization_code" .
                "&client_id=".$request->query->get("client_id") .
                "&client_secret=".$clientSecret .
                "&redirect_uri=".$redirectUrl .
                "&userIdEncoded=".$authInfo["user_id_encoded"]
            );

        }

        $allQueryArray = $request->query->all();
        $allQueryParamNames = array_keys($allQueryArray);
        $queryUrlString = array_reduce($allQueryParamNames, function($prevQuery, $currQueryName) use($allQueryArray){
            return $prevQuery.$currQueryName."=".$allQueryArray[$currQueryName]."&";
        },'');

        $this->view->form = $form->createView();
        $this->view->queryUrlString = substr($queryUrlString,0, strlen($queryUrlString)-1);


    }

    /**
     * @param Request $request
     * @Route("/access_token", name="access_token_path")
     * @return Response
     */
    public function accessToken(Request $request)
    {

        /**
         * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
         */
        $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

        // set necessary post-params via get-params coming directly from form_auth_code_path
        if(!$request->request->get("client_id"))$request->request->set("client_id", $request->query->get("client_id"));
        if(!$request->request->get("client_secret"))$request->request->set("client_secret", $request->query->get("client_secret"));
        if(!$request->request->get("code"))$request->request->set("code", $request->query->get("code"));
        if(!$request->request->get("grant_type"))$request->request->set("grant_type", "authorization_code");
        if(!$request->request->get("redirect_uri"))$request->request->set("redirect_uri", $request->query->get("redirect_uri"));

        $response = $authServerService->getAccessTokenForAuthGrantClient($request);

        if($response->getStatusCode() == Response::HTTP_UNAUTHORIZED){
            throw new HttpException(401, "AUTHORIZATION FAILED");
        }

        return $response;

    }

    protected function sendResponse(Response $response)
    {
        $httpResponse = new JsonResponse();

        $httpResponse->setStatusCode($response->getStatusCode());

        foreach ($response->headers->all() as $key => $value) {
            $httpResponse->headers->set($key, $value, true);
        }

        $httpResponse->setData($response->getContent());
        return $httpResponse;
    }

}
