<?php

use BackendToolkit\Controller\Traits\PaginatorController;
use BackendToolkit\Listing\Filter;
use BackendToolkit\Listing\FilterHandler;
use CustomerManagementFramework\Controller\Admin;
use CustomerManagementFramework\CustomerList\Filter\SearchQuery;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\CustomerList\Filter\CustomerSegment as CustomerSegmentFilter;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\Listing;

class CustomerManagementFramework_DuplicatesController extends Admin
{
    use PaginatorController;

    public function init()
    {
        parent::init();

        \Pimcore\Model\Object\AbstractObject::setHideUnpublished(true);
    }

    public function listAction()
    {
        $this->enableLayout();

        $db = \Pimcore\Db::get();

        $rows = $db->fetchAll("select * from plugin_cmf_potential_duplicates  limit 100");

        $select = $db->select();
        $select
            ->from("plugin_cmf_potential_duplicates",
                [
                    'id',
                    'duplicateCustomerIds',
                    'declinedDuplicateIds',
                    'creationDate',
                    'modificationDate'
                ]
            )
            ->order("id asc")
        ;

        $paginator = new \Zend_Paginator(new \Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($this->getParam('page', 1));

        $this->view->paginator = $paginator;

        $duplicates = [];
        foreach($rows as $row) {
            $duplicateIds = explode(',', $row['duplicateCustomerIds']);

            $duplicateRow = [
                'dbData' => $row,
                'customers' => []
            ];

            foreach($duplicateIds as $id) {
                if ($customer = Factory::getInstance()->getCustomerProvider()->getById($id)) {
                    $duplicateRow['customers'][] = $customer;
                }
            }

            if(sizeof($duplicateRow['customers']) > 1) {
                $duplicates[] = $duplicateRow;
            }

        }

        $this->view->duplicates = $duplicates;
    }

    public function falsePositivesAction()
    {
        $this->enableLayout();
        $db = \Pimcore\Db::get();

        $select = $db->select();
        $select
            ->from("plugin_cmf_duplicates_false_positives",
                [
                    'row1',
                    'row2',
                    'row1Details',
                    'row2Details',
                ]
            )
            ->order("row1 asc")
        ;

        $paginator = new \Zend_Paginator(new \Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage(200);
        $paginator->setCurrentPageNumber($this->getParam('page', 1));

        $this->view->paginator = $paginator;
    }
}
