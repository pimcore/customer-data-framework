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

    /**
     * @param                             $pageSize
     * @param int                         $page
     * @param ExportCustomersFilterParams $params
     *
     * @return array
     */
    public function customers($pageSize, $page = 1, ExportCustomersFilterParams $params);

    /**
     * @param                             $pageSize
     * @param int                         $page
     * @param ExportCustomersFilterParams $params
     *
     * @return array
     */
    public function activities($pageSize, $page = 1, ExportActivitiesFilterParams $params);

    /**
     * @param                             $pageSize
     * @param int                         $page
     * @param ExportCustomersFilterParams $params
     *
     * @return array
     */
    public function deletions($type, $deletionsSinceTimestamp);

    /**
     * @return array
     */
    public function segments(array $params);

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function segmentGroups(array $params);
}