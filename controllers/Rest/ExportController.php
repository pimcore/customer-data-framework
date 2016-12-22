<?php


class CustomerManagementFramework_Rest_ExportController extends \Pimcore\Controller\Action\Webservice
{
    public function jsonAction()
    {
        $export = \CustomerManagementFramework\Factory::getInstance()->getRESTApiExport();

        $result = $export->exportAction($this->getParam('restAction'), $this->getAllParams());
        $this->_helper->json($result);
    }
}
