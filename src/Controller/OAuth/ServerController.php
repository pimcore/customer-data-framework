<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 10:57
 */

namespace CustomerManagementFrameworkBundle\Controller\OAuth;

use CustomerManagementFrameworkBundle\Form\AuthType;
use CustomerManagementFrameworkBundle\Service\AuthorizationServer;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ServerController extends FrontendController
{

    /**
     * REQUEST A NEW AUTH-CODE BY LOGGING IN
     * @param Request $request
     * @Route("/form_auth_code", name="form_auth_code_path")
     * @return RedirectResponse
     * @throws \Exception
     */
    public function formAuthorizeAuthGrantClient(Request $request)
    {

        try {
            $form = $this->createForm(AuthType::class);
            $form->handleRequest($request);

            $clientId = $request->query->get("client_id");
            $redirectUrl = $request->query->get("redirect_uri");
            $responseType = $request->query->get("response_type");

            if (!$clientId) {
                throw new \Exception("GET-PARAM: client_id is missing",400);
            }

            if (!$redirectUrl) {
                throw new \Exception("GET-PARAM: redirect_uri is missing",400);
            }

            if (!$responseType) {
                throw new \Exception("GET-PARAM: response_type is missing",400);
            }

            if ($form->isSubmitted() && $form->isValid()) {

                /**
                 * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
                 */
                $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

                $authServerService->authUser($request->request->get("username"), $request->request->get("password"));

                $redirectResponse = $authServerService->validateClient(AuthorizationServer::$GRANT_TYPE_AUTH_GRANT, $request);

                return $redirectResponse;
            }

            $allQueryArray = $request->query->all();
            $allQueryParamNames = array_keys($allQueryArray);
            $queryUrlString = array_reduce($allQueryParamNames, function ($prevQuery, $currQueryName) use ($allQueryArray) {
                return $prevQuery . $currQueryName . "=" . $allQueryArray[$currQueryName] . "&";
            }, '');

            $this->view->form = $form->createView();
            $this->view->queryUrlString = substr($queryUrlString, 0, strlen($queryUrlString) - 1);
            $this->view->formAction = $this->generateUrl("form_auth_code_path");
            $this->view->pageTitle = "Auth Grant Client";
        }
        catch(\Exception $error){
            return $this->sendJSONError($error);
        }

    }

    /**
     * REQUEST AN ACCESS-TOKEN BY USING AN AUTH-CODE
     * @param Request $request
     * @Route("/access_token", name="access_token_path")
     * @return JsonResponse
     * @throws \Exception
     */
    public function accessToken(Request $request)
    {
        try {
            /**
             * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
             */
            $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

            if (!$request->request->get("client_id")) throw new \Exception("POST-PARAM: client_id is missing",400);
            if (!$request->request->get("client_secret")) throw new \Exception("POST-PARAM: client_secret is missing",400);
            if (!$request->request->get("code")) throw new \Exception("POST-PARAM: code is missing",400);
            if (!$request->request->get("grant_type")) throw new \Exception("POST-PARAM: grant_type is missing",400);
            if (!$request->request->get("redirect_uri")) throw new \Exception("POST-PARAM: redirect_uri is missing",400);

            $response = $authServerService->getAccessTokenForAuthGrantClient($request);

            return $this->sendResponse($response);
        }
        catch (\Exception $error){
            return $this->sendJSONError($error);
        }
    }

    /**
     * REQUEST A NEW ACCESS-TOKEN BY USING AN IMPLICIT GRANT
     * @param Request $request
     * @Route("/form_auth_implicit", name="form_auth_implicit_path")
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function formAuthorizeImplicitGrantClient(Request $request)
    {

        try {
            $form = $this->createForm(AuthType::class);
            $form->handleRequest($request);

            $clientId = $request->query->get("client_id");
            $responseType = $request->query->get("response_type");

            if (!$clientId) {
                throw new \Exception("GET-PARAM: client_id is missing",400);
            }

            if (!$responseType) {
                throw new \Exception("GET-PARAM: response_type is missing",400);
            }

            if ($form->isSubmitted() && $form->isValid()) {

                /**
                 * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
                 */
                $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

                $authServerService->authUser($request->request->get("username"), $request->request->get("password"));

                $redirectResponse = $authServerService->validateClient(AuthorizationServer::$GRANT_TYPE_IMPLICIT_GRANT, $request);

                return $redirectResponse;
            }

            $allQueryArray = $request->query->all();
            $allQueryParamNames = array_keys($allQueryArray);
            $queryUrlString = array_reduce($allQueryParamNames, function ($prevQuery, $currQueryName) use ($allQueryArray) {
                return $prevQuery . $currQueryName . "=" . $allQueryArray[$currQueryName] . "&";
            }, '');

            $this->view->form = $form->createView();
            $this->view->queryUrlString = substr($queryUrlString, 0, strlen($queryUrlString) - 1);
            $this->view->pageTitle = "Implicit Grant Client";
            $this->view->formAction = $this->generateUrl("form_auth_implicit_path");

            $templateAbsolutePath = str_replace("Controller/OAuth", "Resources/views/OAuth/Server/", __DIR__);
            return $this->render($templateAbsolutePath . "formAuthorizeAuthGrantClient.html.php", $this->view->getAllParameters());
        }
        catch(\Exception $error){
            return $this->sendJSONError($error);
        }
    }

    /**
     * REQUEST A NEW ACCESS-TOKEN BY USING AN IMPLICIT GRANT
     * @param Request $request
     * @Route("/form_auth_password", name="form_auth_password_path")
     * @return RedirectResponse|Response|JSONResponse
     * @throws \Exception
     */
    public function authorizePasswordGrantClient(Request $request)
    {
        try {
            $clientId = $request->request->get("client_id");
            $responseType = $request->request->get("grant_type");
            $username = $request->request->get("username");
            $password = $request->request->get("password");

            if(!$clientId){
                throw new \Exception("POST-PARAM: client_id is missing",400);
            }

            if(!$responseType){
                throw new \Exception("POST-PARAM: grant_type is missing",400);
            }

            if(!$username){
                throw new \Exception("POST-PARAM: username is missing",400);
            }

            if(!$password){
                throw new \Exception("POST-PARAM: password is missing",400);
            }


            /**
             * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
             */
            $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

            $authServerService->authUser($request->request->get("username"), $request->request->get("password"));

            $redirectResponse = $authServerService->validateClient(AuthorizationServer::$GRANT_TYPE_PASSWORD_GRANT, $request);

            return $redirectResponse;

        } catch(\Exception $error) {
            return $this->sendJSONError($error);
        }

    }

    /**
     * REQUEST AN SPECIFIC USER-INFO BY USING AN ACCESS-TOKEN, THE USER-INFO CAN BE CONFIGURED IN THE CONFIG.yml (pimcore_customer_management_framework.oauth_server.user_exporter) FILE
     * @param Request $request
     * @Route("/userinfo", name="userinfo_path")
     * @return JSONResponse
     * @throws \Exception
     */
    public function getUserInfo(Request $request){
        try{
            /**
             * @var \CustomerManagementFrameworkBundle\Service\UserInfo $userInfoService
             */
            $userInfoService = \Pimcore::getContainer()->get('CustomerManagementFrameworkBundle\Service\UserInfo');
            $userInfoResponse = $userInfoService->getByAccessTokenRequest($request);

            return new JsonResponse($userInfoResponse);

        } catch(\Exception $error) {
            return $this->sendJSONError($error);
        }
    }

    /**
     * REQUEST A NEW ACCESS-TOKEN BY USING A REFRESH-TOKEN
     * @param Request $request
     * @Route("/refresh_token", name="refresh_token_path")
     * @return JSONResponse
     * @throws \Exception
     */
    public function refreshToken(Request $request)
    {
        try{
            /**
             * @var \CustomerManagementFrameworkBundle\Service\AuthorizationServer $authServerService
             */
            $authServerService = \Pimcore::getContainer()->get("CustomerManagementFrameworkBundle\Service\AuthorizationServer");

            if(!$request->request->get("client_id"))throw new \Exception("POST-PARAM: client_id is missing",400);
            if(!$request->request->get("client_secret"))throw new \Exception("POST-PARAM: client_secret is missing",400);
            if(!$request->request->get("refresh_token"))throw new \Exception("POST-PARAM: refresh_token is missing",400);
            if(!$request->request->get("grant_type"))throw new \Exception("POST-PARAM: grant_type is missing",400);

            $response = $authServerService->getRefreshTokenForAuthGrantClient($request);

            return $this->sendResponse($response);

        } catch(\Exception $error) {
            return $this->sendJSONError($error);
        }
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

    protected function sendJSONError($error){
        $errorEcode = 500;
        if($error instanceof HttpException) {
            $errorEcode = $error->getStatusCode();
        }
        return new JsonResponse(['success'=>false,'error'=>$error->getMessage()],$errorEcode);
    }
}
