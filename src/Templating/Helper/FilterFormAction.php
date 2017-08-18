<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

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
