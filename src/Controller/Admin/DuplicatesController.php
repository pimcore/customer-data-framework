<?php
//
//namespace CustomerManagementFrameworkBundle\Controller\Admin;
//
//use BackendToolkit\Controller\Traits\PaginatorController;
//use CustomerManagementFrameworkBundle\Controller\Admin;
//use CustomerManagementFrameworkBundle\Factory;
//
//class DuplicatesController extends Admin
//{
//    use PaginatorController;
//
//    public function init()
//    {
//        parent::init();
//
//        \Pimcore\Model\Object\AbstractObject::setHideUnpublished(true);
//    }
//
//    public function listAction()
//    {
//        $this->enableLayout();
//
//        $paginator = Factory::getInstance()->getDuplicatesIndex()->getPotentialDuplicates($this->getParam('page', 1), 100, $this->getParam('declined'));
//
//        $this->view->paginator = $paginator;
//        $this->view->duplicates = $paginator->getCurrentItems();
//
//        $duplicatesView = Factory::getInstance()->getCustomerDuplicatesView();
//        $this->view->duplicatesView = $duplicatesView;
//    }
//
//    public function falsePositivesAction()
//    {
//        $this->enableLayout();
//
//        $paginator = Factory::getInstance()->getDuplicatesIndex()->getFalsePositives($this->getParam('page', 1), 200);
//
//        $this->view->paginator = $paginator;
//    }
//
//    public function declineDuplicateAction()
//    {
//        try {
//            Factory::getInstance()->getDuplicatesIndex()->declinePotentialDuplicate($this->getParam('id'));
//            $this->_helper->json(["success" => "true"]);
//        } catch(\Exception $e){
//            $this->_helper->json(["success" => "false"]);
//        }
//    }
//}
