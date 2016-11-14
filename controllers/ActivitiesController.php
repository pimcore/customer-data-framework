<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 13:19
 */

class CustomerManagementFramework_ActivitiesController extends \Pimcore\Controller\Action\Admin {

    public function init() {
        parent::init();

       // setlocale(LC_TIME, 'de');
    }

    public function listAction() {

        $this->enableLayout();

        if($customer = \Pimcore\Model\Object\Customer::getById($this->getParam('customerId'))) {

            $list = \CustomerManagementFramework\Factory::getInstance()->getActivityStore()->getActivityList();

            $list->setCondition("customerId = ?", $customer->getId());

            $paginator = new Zend_Paginator($list);
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($this->getParam('page', 1));
            $this->view->activities = $paginator;
        }
    }

    public function detailAction() {

        $this->enableLayout();

        $this->view->activity = \CustomerManagementFramework\Factory::getInstance()->getActivityStore()->getEntryById($this->getParam('activityId'));
    }
}