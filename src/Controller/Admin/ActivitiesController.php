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

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Paginator\Paginator;

/**
 * @Route("/activities")
 */
class ActivitiesController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->checkPermission('plugin_cmf_perm_activityview');
    }

    /**
     * @param Request $request
     * @Route("/list")
     */
    public function listAction(Request $request, CustomerProviderInterface $customerProvider)
    {
        $types = null;
        $type = null;
        $activities = null;
        $paginator = null;

        if ($customer = $customerProvider->getById($request->get('customerId'))) {
            $list = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityList();
            $list->setCondition('customerId = ' . $customer->getId());
            $list->setOrderKey('activityDate');
            $list->setOrder('desc');

            $select = $list->getQuery();
            $select->reset(QueryBuilder::COLUMNS);
            $select->reset(QueryBuilder::FROM);
            $select->from(
                \CustomerManagementFrameworkBundle\ActivityStore\MariaDb::ACTIVITIES_TABLE,
                ['type' => 'distinct(type)']
            );
            $types = \Pimcore\Db::get()->fetchCol($select);

            if ($type = $request->get('type')) {
                $select = $list->getQuery(false);
                $select->where('type = ?', $type);
            }

            $paginator = new Paginator($list);
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($request->get('page', 1));
        }

        return $this->render(
            'PimcoreCustomerManagementFrameworkBundle:Admin\Activities:list.html.php',
            [
                'types' => $types,
                'type' => $type,
                'activities' => $paginator,
                'customer' => $customer,
                'activityView' => \Pimcore::getContainer()->get('cmf.activity_view'),
            ]
        );
    }

    /**
     * @param Request $request
     * @Route("/detail")
     */
    public function detailAction(Request $request)
    {
        $activity = \Pimcore::getContainer()->get('cmf.activity_store')->getEntryById($request->get('activityId'));

        return $this->render(
            'PimcoreCustomerManagementFrameworkBundle:Admin\Activities:detail.html.php',
            [
                'activity' => $activity,
                'activityView' => \Pimcore::getContainer()->get('cmf.activity_view'),
            ]
        );
    }
}
