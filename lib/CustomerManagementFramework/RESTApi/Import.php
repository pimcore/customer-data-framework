<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 24.10.2016
 * Time: 17:14
 */

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Filter\ExportActivitiesFilterParams;
use CustomerManagementFramework\Filter\ExportCustomersFilterParams;
use CustomerManagementFramework\Service\ObjectToArray;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Pimcore\Placeholder\Object;

class Import implements ImportInterface {

    public function importAction($action, \Zend_Controller_Request_Http $request)
    {
        if(!($request->isPut() || $request->isPost())) {
            return new Response([
                "success" => false,
                "msg" => sprintf("method needs to be PUT or POST", $action)
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
                    return $this->segment($data);

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

        if(empty($data['name'])) {
            return new Response([
                'success' => false,
                'msg' => 'name required'
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if($data['reference'] && Factory::getInstance()->getSegmentManager()->getSegmentGroupByReference($data['reference'], (bool)$data['calculated'])) {
            return new Response([
                'success' => false,
                'msg' => sprintf("duplicate segment group - group with reference '%s' already exists", $data['reference'])
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $segmentGroup = Factory::getInstance()->getSegmentManager()->createSegmentGroup($data['name'], $data['reference'], isset($data['calculated']) ? (bool)$data['calculated'] : false, $data);


        $result = ObjectToArray::getInstance()->toArray($segmentGroup);
        $result['success'] = true;

        return new Response($result);
    }

    public function segment(array $data)
    {
        if(!$data['group']) {
            return new Response([
                'success' => false,
                'msg' => "group required"
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }
        if(!$segmentGroup = CustomerSegmentGroup::getById($data['group'])) {
            return new Response([
                'success' => false,
                'msg' => "group not found"
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if(!$data['name']) {
            return new Response([
                'success' => false,
                'msg' => "name required"
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        if($data['reference'] && Factory::getInstance()->getSegmentManager()->getSegmentByReference($data['reference'], $segmentGroup)) {
            return new Response([
                'success' => false,
                'msg' => sprintf("duplicate segment - segment with reference '%s' already exists in this group", $data['reference'])
            ], Response::RESPONSE_CODE_BAD_REQUEST);
        }

        $data['calculated'] = isset($data['calculated']) ? $data['calculated'] : $segmentGroup->getCalculated();

        $segment = Factory::getInstance()->getSegmentManager()->createSegment($data['name'], $segmentGroup, $data['reference'], (bool)$data['calculated'], $data['subFolder']);

        $result = $segment->getDataForWebserviceExport();
        $result['success'] = true;

        return new Response($result);
    }
}