<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 13:19
 */

use CustomerManagementFramework\Controller\Admin;

class CustomerManagementFramework_ActivitiesController extends Admin
{
    public function init()
    {
        parent::init();
        $this->checkPermission('plugin_customermanagementframework_activityview');
    }

    public function listAction() {

        $this->enableLayout();

        if($customer = \Pimcore\Model\Object\Customer::getById($this->getParam('customerId'))) {

            $list = \CustomerManagementFramework\Factory::getInstance()->getActivityStore()->getActivityList();

            $select = $list->getQuery(false);
            $select->where("customerId = ?", $customer->getId());


            $select = $list->getQuery();
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::FROM);
            $select->from(\CustomerManagementFramework\ActivityStore\MariaDb::ACTIVITIES_TABLE,
                ["type"=>"distinct(type)"]
            );
            $this->view->types = \Pimcore\Db::get()->fetchCol($select);

            if($type = $this->getParam('type')) {
                $select = $list->getQuery(false);
                $select->where("type = ?", $type);
            }

            $paginator = new Zend_Paginator($list);
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($this->getParam('page', 1));
            $this->view->activities = $paginator;
            $this->view->customer = $customer;
            $this->view->type = $type;
        }
    }

    public function detailAction() {

        $this->enableLayout();

        $this->view->activity = \CustomerManagementFramework\Factory::getInstance()->getActivityStore()->getEntryById($this->getParam('activityId'));
    }
}
