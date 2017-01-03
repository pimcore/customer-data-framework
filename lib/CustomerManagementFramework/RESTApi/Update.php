<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 24.10.2016
 * Time: 17:14
 */

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Helper\Objects;
use CustomerManagementFramework\Service\ObjectToArray;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Psr\Log\LoggerInterface;

class Update implements UpdateInterface {

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function updateAction($action, \Zend_Controller_Request_Http $request)
    {
        if(!($request->isPost())) {
            return new Response([
                "success" => false,
                "msg" => sprintf("method needs to be POST", $action)
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $body = $request->getRawBody();
        $data = json_decode($body, true);

        if (is_null($data)) {
            return new Response(['success' => false,
                                 'msg'     => 'please send a valid JSON string in the body of your request'],
                Response::RESPONSE_CODE_BAD_REQUEST);
        }


        try {
            switch($action) {
                case "segment-group":
                    return $this->segmentGroup($data);
                case "segment":
                    //return $this->segment($data);
                case "segments-of-customer":
                    return $this->segmentsOfCustomer($data);

            }
        } catch(\Exception $e) {
            return new Response([
                "success" => false,
                "msg" => $e->getMessage()
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }


        return new Response([
            "success" => false,
            "msg" => sprintf("rest action '%s' not found", $action)
        ], Response::RESPONSE_CODE_NOT_FOUND);
    }

    public function segmentGroup(array $data)
    {
        if(empty($data['id'])) {
            return new Response([
                'success' => false,
                'msg' => 'id required'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if(!$segmentGroup = CustomerSegmentGroup::getByid($data['id'])) {
            return new Response([
                'success' => false,
                'msg' => sprintf('segment group with id %s not found', $data['id'])
            ], Response::RESPONSE_CODE_NOT_FOUND);
        }

        Factory::getInstance()->getSegmentManager()->updateSegmentGroup($segmentGroup, $data);

        $result = ObjectToArray::getInstance()->toArray($segmentGroup);
        $result['success'] = true;

        return new Response($result, Response::RESPONSE_CODE_OK);
    }

    public function segmentsOfCustomer(array $data)
    {
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