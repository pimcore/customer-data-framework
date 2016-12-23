<?php


class CustomerManagementFramework_Rest_ExportController extends \Pimcore\Controller\Action\Webservice
{
    public function jsonAction()
    {
        $export = \CustomerManagementFramework\Factory::getInstance()->getRESTApiExport();

        $result = $export->exportAction($this->getParam('restAction'), $this->getAllParams());

        if($result instanceof \CustomerManagementFramework\RESTApi\Response) {

            $this->getResponse()->setHttpResponseCode($result->getResponseCode());
            $this->_helper->json($result->getData());
        }

    }
}
