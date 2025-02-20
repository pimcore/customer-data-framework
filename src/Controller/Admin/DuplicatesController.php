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

use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\CustomerList\SearchHelper;
use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/duplicates")
 */
class DuplicatesController extends Admin
{
    public function init()
    {
        AbstractObject::setHideUnpublished(true);
    }

    public function onKernelControllerEvent(ControllerEvent $event): void
    {
        parent::onKernelControllerEvent($event);
        $this->checkPermission('plugin_cmf_perm_customerview');
    }

    /**
     * @Route("/list")
     *
     * @throws \Exception
     */
    public function listAction(Request $request, DuplicatesIndexInterface $duplicatesIndex): Response
    {
        // fetch all filters
        $filters = $request->query->all('filter');
        // check if filters exist
        $customerList = null;
        if (!empty($filters)) {
            // build customer listing
            $customerList = $this->getSearchHelper()->getCustomerProvider()->getList();
            $idField = Service::getVersionDependentDatabaseColumnName('id');
            $customerList
                ->setOrderKey($idField)
                ->setOrder('ASC');

            /** @noinspection PhpUnhandledExceptionInspection */
            $this->getSearchHelper()->addListingFilters($customerList, $filters, $this->getPimcoreUser());
        }

        $paginator = $duplicatesIndex->getPotentialDuplicates(
            $request->query->getInt('page', 1),
            50,
            $request->query->getBoolean('declined'),
            $customerList
        );

        return $this->render(
            '@PimcoreCustomerManagementFramework/admin/duplicates/list.html.twig',
            [
                'paginator' => $paginator,
                'paginationVariables' => $paginator instanceof SlidingPaginationInterface ? $paginator->getPaginationData() : [],
                'duplicates' => $paginator->getItems(),
                'duplicatesView' => \Pimcore::getContainer()->get('cmf.customer_duplicates_view'),
                'searchBarFields' => $this->getSearchHelper()->getConfiguredSearchBarFields(),
                'filters' => $filters,
            ]
        );
    }

    /**
     * @Route("/decline/{id}")
     */
    public function declineAction(Request $request): JsonResponse
    {
        try {
            \Pimcore::getContainer()->get('cmf.customer_duplicates_index')->declinePotentialDuplicate(
                $request->attributes->getInt('id')
            );

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    protected function getSearchHelper(): SearchHelper
    {
        return \Pimcore::getContainer()->get(SearchHelper::class);
    }
}
