<?php

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Templating\Helper\Helper;
use Zend\Paginator\Paginator;

class FilterFormAction extends Helper
{
    public function getName()
    {
        return 'FilterFormAction';
    }

    /**
     * @param Paginator|null $paginator
     *
     * @return string
     */
    public function get(Paginator $paginator)
    {
        // reset page when changing filters
        $formActionParams = [
            'page' => null,
            'perPage' => null,
        ];

        if (null !== $paginator && $paginator->getItemCountPerPage() !== 25) {
            $formActionParams['perPage'] = $paginator->getItemCountPerPage();
        }

        /**
         * @var Router $router
         */
        $router = \Pimcore::getContainer()->get('router');
        /**
         * @var Request $request
         */
        $request = \Pimcore::getContainer()->get('request_stack')->getMasterRequest();

        $formAction = $router->generate($request->get('_route'), $formActionParams);

        return $formAction;
    }
}
