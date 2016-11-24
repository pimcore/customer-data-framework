<?php

class CustomerManagementFramework_AuthController extends \Pimcore\Controller\Action
{
    public function loginAction()
    {
    }

    public function hybridauthAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        /** @var \CustomerManagementFramework\Authentication\Sso\DefaultHybridAuthHandler $hybridAuthHandler */
        $hybridAuthHandler = Pimcore::getDiContainer()->get('CustomerManagementFramework\Authentication\Sso\HybridAuthHandler');
        $hybridAuthHandler->authenticate($request);

        $customer = $hybridAuthHandler->getCustomerFromAuthResponse($request);
        if ($customer) {
            dump('FOUND CUSTOMER');
            dump($customer);
        } else {
            $customer = \CustomerManagementFramework\Factory::getInstance()->getCustomerProvider()->create(['key' => 'foobar']);
            $hybridAuthHandler->updateCustomerFromAuthResponse($customer, $request);

            dump('NEW CUSTOMER');
            dump($customer);

            $customer->save();
        }
    }
}
