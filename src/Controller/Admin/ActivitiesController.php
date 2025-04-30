<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\ActivityStore\MariaDb;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Controller\KernelControllerEventInterface;
use Pimcore\Controller\UserAwareController;
use Pimcore\Db;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activities')]
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

    #[Route('/list')]
    public function listAction(Request $request, CustomerProviderInterface $customerProvider): Response
    {

        if ($customer = $customerProvider->getById($request->query->getInt('customerId'))) {
            $list = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityList();
            $list->setCondition('customerId = ' . $customer->getId());
            $list->setOrderKey('activityDate');
            $list->setOrder('desc');

            $select = $list->getQueryBuilder()
                ->from(MariaDb::ACTIVITIES_TABLE)
                ->select('type')
                ->distinct();

            $db = Db::get();
            $types =$db->fetchFirstColumn((string)$select);

            if ($type = $request->query->getString('type')) {
                $list->setCondition('type = ' . $db->quote($type));
            }

            $paginator = $this->paginator->paginate($list, $request->query->getInt('page', 1), 25);

            return $this->render(
                '@PimcoreCustomerManagementFramework/admin/activities/list.html.twig',
                [
                    'types' => $types,
                    'selectedType' => $type,
                    'activities' => $paginator,
                    'paginationVariables' => $paginator instanceof SlidingPaginationInterface ? $paginator->getPaginationData() : [],
                    'customer' => $customer,
                    'activityView' => \Pimcore::getContainer()->get('cmf.activity_view'),
                ]
            );
        }

        throw $this->createNotFoundException();
    }

    #[Route('/detail')]
    public function detailAction(Request $request): Response
    {
        $activityId = $request->query->getInt('activityId');
        $activity = \Pimcore::getContainer()->get('cmf.activity_store')->getEntryById($activityId);

        return $this->render(
            '@PimcoreCustomerManagementFramework/admin/activities/detail.html.twig',
            [
                'activity' => $activity,
                'activityView' => \Pimcore::getContainer()->get('cmf.activity_view'),
            ]
        );
    }
}
