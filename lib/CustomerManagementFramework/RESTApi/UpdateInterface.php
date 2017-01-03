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

interface UpdateInterface {

    public function updateAction($action, \Zend_Controller_Request_Http $request);

    /**
     * @param array $data
     *
     * @return Response
     */
    public function segmentsOfCustomer(array $data);
}