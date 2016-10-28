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

interface IExport {

    public function customers($pageSize, $page = 1, ExportCustomersFilterParams $params);
    public function activities($pageSize, $page = 1, ExportActivitiesFilterParams $params);
    public function deletions($type, $deletionsSinceTimestamp);
}