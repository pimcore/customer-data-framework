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

        $paginator = Factory::getInstance()->getDuplicatesIndex()->getPotentialDuplicates($this->getParam('page', 1), 100);

        $this->view->paginator = $paginator;
        $this->view->duplicates = $paginator->getCurrentItems();

        $duplicatesView = Factory::getInstance()->getCustomerDuplicatesView();
        $this->view->duplicatesView = $duplicatesView;
    }

    public function falsePositivesAction()
    {
        $this->enableLayout();

        $paginator = Factory::getInstance()->getDuplicatesIndex()->getFalsePositives($this->getParam('page', 1), 200);

        $this->view->paginator = $paginator;
    }

    public function declineDuplicateAction()
    {
        try {
            Factory::getInstance()->getDuplicatesIndex()->declinePotentialDuplicate($this->getParam('id'));
            $this->_helper->json(["success" => "true"]);
        } catch(\Exception $e){
            $this->_helper->json(["success" => "false"]);
        }
    }
}
