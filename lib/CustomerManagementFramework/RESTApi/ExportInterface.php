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

interface ExportInterface {

    public function exportAction($action, array $param);

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
}