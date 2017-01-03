<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 24.10.2016
 * Time: 17:14
 */

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\Filter\ExportActivitiesFilterParams;
use CustomerManagementFramework\Filter\ExportCustomersFilterParams;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface ExportInterface {

    public function __construct(LoggerInterface $logger);

    public function exportAction($action, \Zend_Controller_Request_Http $request);

    /**
     * @param                             $pageSize
     * @param int                         $page
     * @param ExportCustomersFilterParams $params
     *
     * @return Response
     */
    public function customers($pageSize, $page = 1, ExportCustomersFilterParams $params);

    /**
     * @param                             $pageSize
     * @param int                         $page
     * @param ExportCustomersFilterParams $params
     *
     * @return Response
     */
    public function activities($pageSize, $page = 1, ExportActivitiesFilterParams $params);

    /**
     * @param                             $pageSize
     * @param int                         $page
     * @param ExportCustomersFilterParams $params
     *
     * @return Response
     */
    public function deletions($type, $deletionsSinceTimestamp);

    /**
     * @return Response
     */
    public function segments(array $params);

    /**
     * @param array $params
     *
     * @return Response
     */
    public function segmentGroups(array $params);

    /**
     * @param CustomerInterface $customer
     * @param ExportCustomersFilterParams $params
     * @return array
     */
    public function hydrateCustomer(CustomerInterface $customer, ExportCustomersFilterParams $params);
}
