<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
