<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 10:57
 */

namespace CustomerManagementFrameworkBundle\Controller\OAuth;

use CustomerManagementFrameworkBundle\Form\AuthType;
use CustomerManagementFrameworkBundle\OAuth\Service\AuthorizationServer;
use CustomerManagementFrameworkBundle\OAuth\Service\UserInfo;
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
     * @param AuthorizationServer $authorizationServer
     * @Route("/form_auth_code", name="cmf_form_auth_code_path")
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse|JsonResponse|RedirectResponse|Response
     */
    public function formAuthorizeAuthGrantClient(Request $request, AuthorizationServer $authorizationServer)
    {

        try {
            $form = $this->createForm(AuthType::class);
            $form->handleRequest($request);

            $clientId = $request->query->get("client_id");
            $redirectUrl = $request->query->get("redirect_uri");
            $responseType = $request->query->get("response_type");

            if (!$clientId) {
                throw new HttpException(400,"GET-PARAM: client_id is missing");
            }

            if (!$redirectUrl) {
                throw new HttpException(400,"GET-PARAM: redirect_uri is missing");
            }

            if (!$responseType) {
                throw new HttpException(400,"GET-PARAM: response_type is missing");
            }

            if ($form->isSubmitted() && $form->isValid()) {

                $authorizationServer->authUser($request->request->get("username"), $request->request->get("password"));

                $redirectResponse = $authorizationServer->validateClient(AuthorizationServer::GRANT_TYPE_AUTH_GRANT, $request);

                return $redirectResponse;
            }

            return $this->render(  "PimcoreCustomerManagementFrameworkBundle:OAuth/Server:formAuthorizeAuthGrantClient.html.php", [
                'form' => $form->createView(),
                'pageTitle' => "Auth Grant Client",
                'formAction' => $this->generateUrl("cmf_form_auth_code_path", $request->query->all())
            ]);

        }
        catch(\Exception $error){
            return $this->sendJSONError($error);
        }

    }

    /**
     * REQUEST AN ACCESS-TOKEN BY USING AN AUTH-CODE
     * @param Request $request
     * @param AuthorizationServer $authorizationServer
     * @Route("/access_token", name="cmf_access_token_path")
     * @return JsonResponse
     * @throws \Exception
     */
    public function accessToken(Request $request, AuthorizationServer $authorizationServer)
    {
        try {

            if (!$request->request->get("client_id")) throw new HttpException(400,"POST-PARAM: client_id is missing");
            if (!$request->request->get("client_secret")) throw new HttpException(400,"POST-PARAM: client_secret is missing");
            if (!$request->request->get("code")) throw new HttpException(400,"POST-PARAM: code is missing");
            if (!$request->request->get("grant_type")) throw new HttpException(400,"POST-PARAM: grant_type is missing");
            if (!$request->request->get("redirect_uri")) throw new HttpException(400,"POST-PARAM: redirect_uri is missing");

            $response = $authorizationServer->getAccessTokenForAuthGrantClient($request);

            return $this->sendResponse($response);
        }
        catch (\Exception $error){
            return $this->sendJSONError($error);
        }
    }

    /**
     * REQUEST A NEW ACCESS-TOKEN BY USING AN IMPLICIT GRANT
     * @param Request $request
     * @param AuthorizationServer $authorizationServer
     * @Route("/form_auth_implicit", name="cmf_form_auth_implicit_path")
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function formAuthorizeImplicitGrantClient(Request $request, AuthorizationServer $authorizationServer)
    {

        try {
            $form = $this->createForm(AuthType::class);
            $form->handleRequest($request);

            $clientId = $request->query->get("client_id");
            $responseType = $request->query->get("response_type");

            if (!$clientId) {
                throw new HttpException(400,"GET-PARAM: client_id is missing");
            }

            if (!$responseType) {
                throw new HttpException(400,"GET-PARAM: response_type is missing");
            }

            if ($form->isSubmitted() && $form->isValid()) {

                $authorizationServer->authUser($request->request->get("username"), $request->request->get("password"));

                $redirectResponse = $authorizationServer->validateClient(AuthorizationServer::GRANT_TYPE_IMPLICIT_GRANT, $request);

                return $redirectResponse;
            }

            return $this->render(  "PimcoreCustomerManagementFrameworkBundle:OAuth/Server:formAuthorizeAuthGrantClient.html.php", [
                'form' => $form->createView(),
                'pageTitle' => "Implicit Grant Client",
                'formAction' => $this->generateUrl("cmf_form_auth_implicit_path", $request->query->all())
            ]);
        }
        catch(\Exception $error){
            return $this->sendJSONError($error);
        }
    }

    /**
     * REQUEST A NEW ACCESS-TOKEN BY USING AN IMPLICIT GRANT
     * @param Request $request
     * @param AuthorizationServer $authorizationServer
     * @Route("/form_auth_password", name="cmf_form_auth_password_path")
     * @return RedirectResponse|Response|JSONResponse
     * @throws \Exception
     */
    public function authorizePasswordGrantClient(Request $request, AuthorizationServer $authorizationServer)
    {
        try {
            $clientId = $request->request->get("client_id");
            $responseType = $request->request->get("grant_type");
            $username = $request->request->get("username");
            $password = $request->request->get("password");

            if(!$clientId){
                throw new HttpException(400, "POST-PARAM: client_id is missing");
            }

            if(!$responseType){
                throw new HttpException(400, "POST-PARAM: grant_type is missing");
            }

            if(!$username){
                throw new HttpException(400, "POST-PARAM: username is missing");
            }

            if(!$password){
                throw new HttpException(400,"POST-PARAM: password is missing");
            }

            $authorizationServer->authUser($request->request->get("username"), $request->request->get("password"));

            $redirectResponse = $authorizationServer->validateClient(AuthorizationServer::GRANT_TYPE_PASSWORD_GRANT, $request);

            return $redirectResponse;

        } catch(\Exception $error) {
            return $this->sendJSONError($error);
        }

    }

    /**
     * REQUEST AN SPECIFIC USER-INFO BY USING AN ACCESS-TOKEN, THE USER-INFO CAN BE CONFIGURED IN THE CONFIG.yml (pimcore_customer_management_framework.oauth_server.user_exporter) FILE
     * @param Request $request
     * @param UserInfo $userInfo
     * @Route("/userinfo", name="cmf_userinfo_path")
     * @return JSONResponse
     * @throws \Exception
     */
    public function getUserInfo(Request $request, UserInfo $userInfo){
        try{

            $userInfoResponse = $userInfo->getByAccessTokenRequest($request);
            return new JsonResponse($userInfoResponse);

        } catch(\Exception $error) {
            return $this->sendJSONError($error);
        }
    }

    /**
     * REQUEST A NEW ACCESS-TOKEN BY USING A REFRESH-TOKEN
     * @param Request $request
     * @param AuthorizationServer $authorizationServer
     * @Route("/refresh_token", name="cmf_refresh_token_path")
     * @return JSONResponse
     * @throws \Exception
     */
    public function refreshToken(Request $request, AuthorizationServer $authorizationServer)
    {
        try{

            if(!$request->request->get("client_id"))throw new HttpException(400, "POST-PARAM: client_id is missing");
            if(!$request->request->get("client_secret"))throw new HttpException(400, "POST-PARAM: client_secret is missing");
            if(!$request->request->get("refresh_token"))throw new HttpException(400, "POST-PARAM: refresh_token is missing");
            if(!$request->request->get("grant_type"))throw new HttpException(400, "POST-PARAM: grant_type is missing");

            $response = $authorizationServer->getRefreshTokenForAuthGrantClient($request);

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

        $jsonPayload = json_decode($response->getContent());
        $jsonPayload->success = true;

        $httpResponse->setData($jsonPayload);


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
