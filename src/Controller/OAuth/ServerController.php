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
use Pimcore\Tool\Console;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ServerController extends FrontendController
{

    /**
     * @param Request $request
     * @Route("/form_auth_code", name="form_auth_code_path")
     * @return RedirectResponse
     */
    public function formAuthorizeClient(Request $request)
    {
        $form = $this->createForm(AuthType::class);
        $form->handleRequest($request);

        $redirectUrl = $request->query->get("redirect_url");
        $responseType = $request->query->get("response_type");
        $state = $request->query->get("state");
        $scope = $request->query->get("scope");

        if($form->isSubmitted() && $form->isValid()){
            $authServerService = new AuthorizationServer();

            $request->request->set("response_type", $responseType);
            $request->request->set("scope", "basic");
            $request->request->set("redirect_uri", $redirectUrl);

            if($state){
                $request->request->set("state", $state);
            }
            if($scope){
                $request->request->set("scope", $scope);
            }

            $response = $authServerService->validateClient(AuthorizationServer::$GRANT_TYPE_AUTH_CODE, $request);

            $urlComps = parse_url($response->getHeaders()["Location"][0]);

            $query = $urlComps["query"];
            $authCode = null;

            preg_match('/code=([a-zA-Z0-9]+)/', $query, $matches);

            $request->request->set("grant_type", "authorization_code");

            if(count($matches)>1){
                $request->request->set("code", $matches[1]);
            }

            $response = $authServerService->getAccessTokenForClient($request);

            $json = json_decode($response->getContent());
            $json->redirect_url = $redirectUrl;

            $response->setContent(json_encode($json));

            return $this->sendResponse($response);
        }

        $allQueryArray = $request->query->all();
        $allQueryParamNames = array_keys($allQueryArray);
        $queryUrlString = array_reduce($allQueryParamNames, function($prevQuery, $currQueryName) use($allQueryArray){
            return $prevQuery.$currQueryName."=".$allQueryArray[$currQueryName]."&";
        },'');

        $this->view->form = $form->createView();
        $this->view->queryUrlString = substr($queryUrlString,0, strlen($queryUrlString)-1);

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
