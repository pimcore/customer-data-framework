<?php


class CustomerManagementFramework_Rest_ImportController extends \Pimcore\Controller\Action\Webservice
{
    public function jsonAction()
    {
        $import = \CustomerManagementFramework\Factory::getInstance()->getRESTApiImport();

        $result = $import->importAction($this->getParam('restAction'), $this->getRequest());

        if($result instanceof \CustomerManagementFramework\RESTApi\Response) {

            $this->getResponse()->setHttpResponseCode($result->getResponseCode());
            $this->_helper->json($result->getData());
        }

    }
}
