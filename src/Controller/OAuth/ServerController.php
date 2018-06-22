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
     * @throws \Exception
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

            $encypKey = base64_encode(random_bytes(32));
            $redirectResponse = $authServerService->validateClient(AuthorizationServer::$GRANT_TYPE_AUTH_CODE, $request, $encypKey);

            return $redirectResponse;
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
     * @return JsonResponse
     * @throws \Exception
     */
    public function accessToken(Request $request)
    {
        /**
         * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
         */
        $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

        if(!$request->request->get("client_id"))throw new HttpException(400, "POST-PARAM: client_id is missing");
        if(!$request->request->get("client_secret"))throw new HttpException(400, "POST-PARAM: client_secret is missing");
        if(!$request->request->get("code"))throw new HttpException(400, "POST-PARAM: code is missing");
        if(!$request->request->get("grant_type"))throw new HttpException(400, "POST-PARAM: grant_type is missing");
        if(!$request->request->get("redirect_uri"))throw new HttpException(400, "POST-PARAM: redirect_uri is missing");

        $response = $authServerService->getAccessTokenForAuthGrantClient($request);

        return $this->sendResponse($response);

    }

    /**
     * @param Request $request
     * @Route("/userinfo", name="userinfo_path")
     * @return JSONResponse
     * @throws \Exception
     */
    public function getUserInfo(Request $request){

        /**
         * @var \CustomerManagementFrameworkBundle\Service\UserInfo $userInfoService
         */
        $userInfoService = \Pimcore::getContainer()->get('CustomerManagementFrameworkBundle\Service\UserInfo');
        $userInfoResponse = $userInfoService->getByAccessTokenRequest($request);

        return new JsonResponse($userInfoResponse);
    }

    /**
     * @param Request $request
     * @Route("/refresh_token", name="refresh_token_path")
     * @return JSONResponse
     * @throws \Exception
     */
    public function refreshToken(Request $request)
    {
        /**
         * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
         */
        $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

        if(!$request->request->get("client_id"))throw new HttpException(400, "POST-PARAM: client_id is missing");
        if(!$request->request->get("client_secret"))throw new HttpException(400, "POST-PARAM: client_secret is missing");
        if(!$request->request->get("refresh_token"))throw new HttpException(400, "POST-PARAM: refresh_token is missing");
        if(!$request->request->get("grant_type"))throw new HttpException(400, "POST-PARAM: grant_type is missing");

        $response = $authServerService->getRefreshTokenForAuthGrantClient($request);

        return $this->sendResponse($response);

    }

    protected function sendResponse(Response $response)
    {
        $httpResponse = new JsonResponse();

        $httpResponse->setStatusCode($response->getStatusCode());

        foreach ($response->headers->all() as $key => $value) {
            $httpResponse->headers->set($key, $value, true);
        }

        $httpResponse->setData(json_decode($response->getContent()));


        return $httpResponse;
    }

}
