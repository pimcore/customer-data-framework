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

class Export implements IExport {


    public function customers($pageSize, $page = 1, ExportCustomersFilterParams $params) {

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

        $result = Factory::getInstance()->getActivityStore()->getActivitiesData($pageSize, $page, $params);
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
            $data[] = ObjectToArray::getInstance()->toArray($segment);
        }

        
        $result['data'] = $data;

        return $result;
    }
}