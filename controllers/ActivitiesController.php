<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 13:19
 */

class CustomerManagementFramework_ActivitiesController extends \Pimcore\Controller\Action\Admin {

    public function listAction() {

        $this->enableLayout();

        if($customer = \Pimcore\Model\Object\Customer::getById($this->getParam('customerId'))) {

            $list = \CustomerManagementFramework\Factory::getInstance()->getActivityStore()->getActivityList();
            $this->view->activities = $list;
        }
    }
}