<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Traits\LoggerAware;
use Symfony\Component\Routing\RouteCollection;

class SegmentsOfCustomerHandler extends AbstractRoutingHandler
{
    use LoggerAware;

    protected function getRoutes()
    {
        $routes = new RouteCollection();

        $routes->add(
            'update',
            $this->createRoute('POST', '/', 'updateRecords')
        );

        return $routes;
    }

    /**
     * POST /segments-of-customer
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array                         $params
     */
    protected function updateRecords(\Zend_Controller_Request_Http $request, array $params = []){

        $data = $this->getRequestData($request);

        if(empty($data['customerId'])) {
            return new Response([
                'success' => false,
                'msg' => 'customerId required'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $customerClass = Factory::getInstance()->getCustomerProvider()->getCustomerClassName();

        if(!$customer = $customerClass::getById($data['customerId'])) {
            return new Response([
                'success' => false,
                'msg' => sprintf('customer with id %s not found', $data['customerId'])
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $addSegments = [];
        if(is_array($data['addSegments'])) {
            foreach($data['addSegments'] as $segmentId) {
                if($segment = \Pimcore\Model\Object\CustomerSegment::getById($segmentId)) {
                    $addSegments[] = $segment;
                }
            }
        }

        $deleteSegments = [];
        if(is_array($data['removeSegments'])) {
            foreach($data['removeSegments'] as $segmentId) {
                if($segment = \Pimcore\Model\Object\CustomerSegment::getById($segmentId)) {
                    $deleteSegments[] = $segment;
                }
            }
        }

        Factory::getInstance()->getSegmentManager()->mergeSegments($customer, $addSegments, $deleteSegments, "REST update API: segments-of-customer action");


        return new Response(['success'=>true], Response::RESPONSE_CODE_OK);
    }



}
