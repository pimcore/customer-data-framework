<?php


class CustomerManagementFramework_Rest_UpdateController extends \Pimcore\Controller\Action\Webservice
{
    public function jsonAction()
    {
        $import = \CustomerManagementFramework\Factory::getInstance()->getRESTApiUpdate();

        $result = $import->updateAction($this->getParam('restAction'), $this->getRequest());

        if($result instanceof \CustomerManagementFramework\RESTApi\Response) {

            $this->getResponse()->setHttpResponseCode($result->getResponseCode());
            $this->_helper->json($result->getData());
        }

    }
}
