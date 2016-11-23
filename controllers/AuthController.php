<?php

class CustomerManagementFramework_AuthController extends \Pimcore\Controller\Action
{
    public function loginAction()
    {
    }

    public function externalAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        $provider = $this->getParam('provider');
        $adapter  = \Pimcore\Tool\HybridAuth::authenticate($provider);

        if ($adapter->adapter instanceof Hybrid_Providers_Twitter || $adapter->adapter instanceof Hybrid_Providers_Google) {
            dump($adapter->getUserProfile());
        }
    }
}
