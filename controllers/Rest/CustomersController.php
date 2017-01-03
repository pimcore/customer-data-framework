<?php

use CustomerManagementFramework\RESTApi\CrudInterface;
use CustomerManagementFramework\RESTApi\Response;

/**
 * @method Zend_Controller_Request_Http getRequest()
 */
class CustomerManagementFramework_Rest_CustomersController extends \Pimcore\Controller\Action\Webservice
{
    /**
     * @return CrudInterface
     */
    protected function getHandler()
    {
        return \CustomerManagementFramework\Factory::getInstance()->getRESTApiCustomers();
    }

    public function jsonAction()
    {
        $handler  = $this->getHandler();
        $request  = $this->getRequest();
        $response = $this->handleRequest($request, $handler);

        if (null === $response) {
            $response = new Response([
                'success' => false,
                'msg'     => sprintf('Method %s is not supported', $request->getMethod())
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $this->getResponse()->setHttpResponseCode($response->getResponseCode());
        $this->_helper->json($response->getData());
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @param CrudInterface $handler
     * @return Response|null
     */
    protected function handleRequest(Zend_Controller_Request_Http $request, CrudInterface $handler)
    {
        // TODO can we build a new handler via DI with containing the current request without the need to set the request manually
        // state of the handler is not always predictable this way
        $handler->setRequest($request);

        $id       = $request->getParam('id');
        $record   = null;
        $data     = [];

        // check if requests needing data have a JSON body
        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            try {
                $data = $this->getRequestData();
            } catch (Exception $e) {
                return new Response([
                    'success' => false,
                    'msg'     => $e->getMessage()
                ], Response::RESPONSE_CODE_BAD_REQUEST);
            }
        }

        // load record if an ID was passed and method supports a record
        if (in_array($request->getMethod(), ['GET', 'PUT', 'DELETE']) && $id) {
            $record = $handler->loadRecord($id);

            if (!$record) {
                return new Response([
                    'success' => false,
                    'msg' => sprintf('Record with ID %d was not found', $id)
                ], Response::RESPONSE_CODE_NOT_FOUND);
            }
        }

        // make sure resources needing a record have a record loaded
        if (in_array($request->getMethod(), ['PUT', 'DELETE']) && !$record) {
            return new Response([
                'success' => false,
                'msg' => 'Missing record to update/delete'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        /** @var Response $response */
        $response = null;

        switch ($request->getMethod()) {
            case 'GET':
                if ($record) {
                    $response = $handler->readRecord($record);
                } else {
                    $response = $handler->listRecords();
                }
                break;

            case 'POST':
                $response = $handler->createRecord($data);
                break;

            case 'PUT':
                $response = $handler->updateRecord($record, $data);
                break;

            case 'DELETE':
                $response = $handler->deleteRecord($record);
                break;
        }

        return $response;
    }

    /**
     * @return array
     */
    protected function getRequestData()
    {
        $body = $this->getRequest()->getRawBody();
        $data = json_decode($body, true);

        if (is_null($data)) {
            throw new InvalidArgumentException('Response body is no valid JSON');
        }

        return $data;
    }

    /**
     * @param Response $response
     */
    protected function handleResponse(Response $response)
    {
        $this->getResponse()->setHttpResponseCode($response->getResponseCode());
        $this->_helper->json($response->getData());
    }
}
