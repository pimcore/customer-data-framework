<?php

namespace CustomerManagementFrameworkBundle\RESTApi;

use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Symfony\Component\Routing\RouteCollection;

class DeletionsHandler extends AbstractRoutingHandler
{
    use LoggerAware;

    protected function getRoutes()
    {
        $routes = new RouteCollection();

        $routes->add(
            'list',
            $this->createRoute('GET', '/', 'listRecords')
        );

        return $routes;
    }

    /**
     * POST /deletions
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array                         $params
     */
    protected function listRecords(\Zend_Controller_Request_Http $request, array $params = []){

        $entityType = $request->getparam('entityType');
        $deletionsSinceTimestamp = $request->getParam('deletionsSinceTimestamp');

        $timestamp = time();

        if(!$entityType) {
            return new Response([
                'success' => false,
                'msg' => 'parameter entityType is required'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if(!in_array($entityType, ['activities', 'customers'])) {
            return new Response([
                'success' => false,
                'msg' => 'entityType must be activities or customers'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $result = Factory::getInstance()->getActivityStore()->getDeletionsData($entityType, $deletionsSinceTimestamp);
        $result['success'] = true;
        $result['timestamp'] = $timestamp;

        return new Response($result);
    }



}
