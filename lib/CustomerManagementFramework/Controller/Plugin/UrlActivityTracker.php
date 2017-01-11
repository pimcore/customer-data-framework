<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.12.2016
 * Time: 17:15
 */

namespace CustomerManagementFramework\Controller\Plugin;

use CustomerManagementFramework\Factory;
use Zend_Controller_Request_Abstract;

class UrlActivityTracker extends \Zend_Controller_Plugin_Abstract {

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        if(!$request->getParam('cmfa') || !$request->getParam('cmfc')) {
            return;
        }

        Factory::getInstance()->getActivityUrlTracker()->trackActivity($request->getParam('cmfc'), $request->getParam('cmfa'), $request->getParams());
    }
}