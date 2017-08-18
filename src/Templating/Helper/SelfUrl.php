<?php

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Templating\Helper\Helper;

class SelfUrl extends Helper
{
    public function getName()
    {
        return 'selfUrl';
    }

    /**
     * Return URL to current action without any params
     *
     * @param Request $request
     * @param bool $reset
     * @param array $params
     * @param bool $includeModule
     *
     * @return mixed
     */
    public function get($reset = true, array $params = [])
    {
        /**
         * @var Router $router
         */
        $router = \Pimcore::getContainer()->get('router');
        /**
         * @var Request $request
         */
        $request = \Pimcore::getContainer()->get('request_stack')->getMasterRequest();

        if (!$reset) {
            $params = array_merge($request->query->all(), $params);
        }

        return $router->generate($request->get('_route'), $params);
    }
}
