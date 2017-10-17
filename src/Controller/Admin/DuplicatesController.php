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

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\Factory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/duplicates")
 */
class DuplicatesController extends Admin
{
    public function init()
    {
        \Pimcore\Model\DataObject\AbstractObject::setHideUnpublished(true);
    }

    /**
     * @param Request $request
     * @Route("/list")
     */
    public function listAction(Request $request)
    {
        $paginator = \Pimcore::getContainer()->get('cmf.customer_duplicates_index')->getPotentialDuplicates(
            $request->get('page', 1),
            50,
            $request->get('declined')
        );

        return $this->render(
            'PimcoreCustomerManagementFrameworkBundle:Admin\Duplicates:list.html.php',
            [
                'paginator' => $paginator,
                'duplicates' => $paginator->getCurrentItems(),
                'duplicatesView' => \Pimcore::getContainer()->get('cmf.customer_duplicates_view'),
            ]
        );
    }

    public function falsePositivesAction()
    {
        $this->enableLayout();

        $paginator = Factory::getInstance()->getDuplicatesIndex()->getFalsePositives($this->getParam('page', 1), 200);

        $this->view->paginator = $paginator;
    }

    /**
     * @param Request $request
     * @Route("/decline/{id}")
     */
    public function declineAction(Request $request)
    {
        try {
            \Pimcore::getContainer()->get('cmf.customer_duplicates_index')->declinePotentialDuplicate(
                $request->get('id')
            );

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
