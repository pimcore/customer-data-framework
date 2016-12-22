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
use Pimcore\Placeholder\Object;

class Export implements ExportInterface {

    public function exportAction($action, array $param)
    {
        switch($action) {
            case "customers":

                $limit = intval($param['pageSize'] ? : 100);
                $offset = intval($param['page'] ? : 1);

                $params = new \CustomerManagementFramework\Filter\ExportCustomersFilterParams;
                $params->setIncludeActivities($param['includeActivities'] ? true : false);
                $params->setSegments($param['segments']);
                $params->setAllParams($param);

                return $this->customers($limit,$offset,$params);

            case "activities":
                $pageSize = intval($param['pageSize'] ? : 100);
                $page = intval($param['page'] ? : 1);

                $params = new \CustomerManagementFramework\Filter\ExportActivitiesFilterParams();
                $params->setType($param['type'] ? : false);
                $params->setModifiedSinceTimestamp($param['modifiedSinceTimestamp']);
                $params->setAllParams($param);

                return $this->activities($pageSize, $page, $params);
            case "deletions":

                $entityType = $param['entityType'];
                $deletionsSinceTimestamp = $param['deletionsSinceTimestamp'];

                return $this->deletions($entityType, $deletionsSinceTimestamp);
            case "segments":

                return $this->segments($param);
            case "segment-groups":

                return $this->segmentGroups($param);

        }
    }

    public function customers($pageSize, $page = 1, ExportCustomersFilterParams $params)
    {

        if($params->getSegments()) {
            $customers = Factory::getInstance()->getSegmentManager()->getCustomersBySegmentIds($params->getSegments());
        } else {
            $customers = new \Pimcore\Model\Object\Customer\Listing;
        }

        $customers->setOrderKey('o_id');
        $customers->setOrder('asc');
        $customers->setUnpublished(false);

        $paginator = new \Zend_Paginator($customers);
        $paginator->setItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($page);

        $timestamp = time();

        $result = [];
        foreach($paginator as $customer) {
            $c = $customer->cmfToArray();

            if($params->getIncludeActivities()) {
                $c['activities'] = Factory::getInstance()->getActivityStore()->getActivityDataForCustomer($customer);
            }

            $result[] = $c;
        }

        return [
            'page' => $page,
            'totalPages' => $paginator->getPages()->pageCount,
            'timestamp' => $timestamp,
            'data' => $result
        ];
    }

    public function activities($pageSize, $page = 1, ExportActivitiesFilterParams $params)
    {

        $result = Factory::getInstance()->getActivityStore()->getActivitiesDataForWebservice($pageSize, $page, $params);
        $result['success'] = true;

        return $result;
    }

    public function deletions($entityType, $deletionsSinceTimestamp) {

        $timestamp = time();

        if(!$entityType) {
            return [
                'success' => false,
                'msg' => 'parameter entityType is required'
            ];
        }

        if(!in_array($entityType, ['activities', 'customers'])) {
            return [
                'success' => false,
                'msg' => 'entityType must be activities or customers'
            ];
        }

        $result = Factory::getInstance()->getActivityStore()->getDeletionsData($entityType, $deletionsSinceTimestamp);
        $result['success'] = true;
        $result['timestamp'] = $timestamp;

        return $result;
    }

    public function segments(array $params) {

        $timestamp = time();

        $result['success'] = true;
        $result['timestamp'] = $timestamp;

        $data = [];
        foreach(Factory::getInstance()->getSegmentManager()->getSegments($params) as $segment) {
            $segment = ObjectToArray::getInstance()->toArray($segment);
            if($segment['group']) {
                $segment['group'] = $segment['group']['id'];
            }
            $data[] = $segment;
        }

        
        $result['data'] = $data;

        return $result;
    }

    public function segmentGroups(array $params) {

    $timestamp = time();

    $result['success'] = true;
    $result['timestamp'] = $timestamp;

    $data = [];
    foreach(Factory::getInstance()->getSegmentManager()->getSegmentGroups($params) as $segment) {

        $data[] = ObjectToArray::getInstance()->toArray($segment);
    }


    $result['data'] = $data;

    return $result;
}
}