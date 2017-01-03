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
use Psr\Log\LoggerInterface;

class Export implements ExportInterface {

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function exportAction($action, \Zend_Controller_Request_Http $request)
    {
        switch($action) {
            case "customers":

                $limit = intval($request->getParam('pageSize', 100));
                $offset = intval($request->getParam('page', 1));

                $params = new \CustomerManagementFramework\Filter\ExportCustomersFilterParams;
                $params->setIncludeActivities($request->getParam('includeActivities') == 'true' ? true : false);
                $params->setSegments($request->getParam('segments'));
                $params->setAllParams($request->getParams());

                return $this->customers($limit,$offset,$params);

            case "activities":
                $pageSize = intval($request->getParam('pageSize', 100));
                $page = intval($request->getParam('page', 1));

                $params = new \CustomerManagementFramework\Filter\ExportActivitiesFilterParams();
                $params->setType($request->getParam('type', false));
                $params->setModifiedSinceTimestamp($request->getParam('modifiedSinceTimestamp'));
                $params->setAllParams($request->getParams());

                return $this->activities($pageSize, $page, $params);
            case "deletions":

                $entityType = $request->getParam('entityType');
                $deletionsSinceTimestamp = $request->getParam('deletionsSinceTimestamp');

                return $this->deletions($entityType, $deletionsSinceTimestamp);
            case "segments":

                return $this->segments($request->getParams());
            case "segment-groups":

                return $this->segmentGroups($request->getParams());

        }

        return new Response([
            "success" => false,
            "msg" => sprintf("rest action '%s' not found", $action)
        ], Response::RESPONSE_CODE_NOT_FOUND);
    }

    public function customers($pageSize, $page = 1, ExportCustomersFilterParams $params)
    {

        if($params->getSegments()) {
            $customers = Factory::getInstance()->getSegmentManager()->getCustomersBySegmentIds($params->getSegments());
        } else {
            $customers = Factory::getInstance()->getCustomerProvider()->getList();
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

        return new Response([
            'page' => $page,
            'totalPages' => $paginator->getPages()->pageCount,
            'timestamp' => $timestamp,
            'data' => $result
        ]);
    }

    public function activities($pageSize, $page = 1, ExportActivitiesFilterParams $params)
    {

        $result = Factory::getInstance()->getActivityStore()->getActivitiesDataForWebservice($pageSize, $page, $params);
        $result['success'] = true;

        return new Response($result);
    }

    public function deletions($entityType, $deletionsSinceTimestamp) {

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

    public function segments(array $params) {

        $timestamp = time();

        $result['success'] = true;
        $result['timestamp'] = $timestamp;

        $data = [];
        foreach(Factory::getInstance()->getSegmentManager()->getSegments($params) as $segment) {
            $data[] = $segment->getDataForWebserviceExport();
        }

        
        $result['data'] = $data;

        return new Response($result);
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

        return new Response($result);
    }
}