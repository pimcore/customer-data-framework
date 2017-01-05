<?php

namespace CustomerManagementFramework\Controller\Rest;

use CustomerManagementFramework\RESTApi\Exception\ExceptionInterface;
use CustomerManagementFramework\RESTApi\HandlerInterface;
use CustomerManagementFramework\RESTApi\Response;
use Pimcore\Controller\Action\Webservice;

/**
 * @method \Zend_Controller_Request_Http getRequest()
 */
abstract class RestHandlerController extends Webservice
{
    /**
     * @return HandlerInterface
     */
    abstract protected function getHandler();

    public function jsonAction()
    {
        $handler  = $this->getHandler();
        $response = null;

        try {
            $response = $handler->handle($this->getRequest());
        } catch (ExceptionInterface $e) {
            $response = new Response([
                'success' => false,
                'msg'     => $e->getMessage()
            ], $e->getCode() > 0 ? $e->getCode() : 400);
        }

        $this->sendResponse($response);
    }

    /**
     * @param Response $response
     */
    protected function sendResponse(Response $response)
    {
        $httpResponse = $this->getResponse();
        $httpResponse->setHttpResponseCode($response->getResponseCode());

        foreach ($response->getHeaders() as $key => $value) {
            $httpResponse->setHeader($key, $value, true);
        }

        $this->_helper->json($response->getData());
    }
}
