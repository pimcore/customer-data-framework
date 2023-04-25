<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\ActivityStore\MariaDb;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Controller\KernelControllerEventInterface;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/activities")
 */
class ActivitiesController extends UserAwareController implements KernelControllerEventInterface
{
    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    public function onKernelControllerEvent(ControllerEvent $event): void
    {
        $this->checkPermission('plugin_cmf_perm_activityview');
    }

    /**
     * @Route("/list")
     */
    public function listAction(Request $request, CustomerProviderInterface $customerProvider): Response
    {
        if ($customer = $customerProvider->getById($request->get('customerId'))) {
            $list = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityList();
            $list->setCondition('customerId = ' . $customer->getId());
            $list->setOrderKey('activityDate');
            $list->setOrder('desc');

            $select = $list->getQueryBuilder()
                ->resetQueryParts(['select', 'from'])
                ->from(MariaDb::ACTIVITIES_TABLE)
                ->select('type')
                ->distinct();

            $types = \Pimcore\Db::get()->fetchFirstColumn((string)$select);

            if ($type = $request->get('type')) {
                $select = $list->getQueryBuilder(false);
                $select->andWhere('type = ' . $list->quote($type));
                $list->setCondition((string) $select->getQueryPart('where'));
            }

            $paginator = $this->paginator->paginate($list, $request->get('page', 1), 25);

            return $this->render(
                '@PimcoreCustomerManagementFramework/admin/activities/list.html.twig',
                [
                    'types' => $types,
                    'selectedType' => $type,
                    'activities' => $paginator,
                    'paginationVariables' => $paginator->getPaginationData(),
                    'customer' => $customer,
                    'activityView' => \Pimcore::getContainer()->get('cmf.activity_view'),
                ]
            );
        }

        throw $this->createNotFoundException();
    }

    /**
     * @Route("/detail")
     */
    public function detailAction(Request $request): Response
    {
        $activity = \Pimcore::getContainer()->get('cmf.activity_store')->getEntryById($request->get('activityId'));

        return $this->render(
            '@PimcoreCustomerManagementFramework/admin/activities/detail.html.twig',
            [
                'activity' => $activity,
                'activityView' => \Pimcore::getContainer()->get('cmf.activity_view'),
            ]
        );
    }
}
