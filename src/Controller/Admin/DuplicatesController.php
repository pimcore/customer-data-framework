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
use CustomerManagementFrameworkBundle\CustomerDuplicatesView\DefaultCustomerDuplicatesView;
use CustomerManagementFrameworkBundle\CustomerList\SearchHelper;
use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @Route("/duplicates")
 */
class DuplicatesController extends Admin
{
    protected SearchHelper $searchHelper;

    public function init()
    {
        \Pimcore\Model\DataObject\AbstractObject::setHideUnpublished(true);
    }

    /**
     * @param Request $request
     * @param DuplicatesIndexInterface $duplicatesIndex
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     * @Route("/list")
     */
    public function listAction(
        Request $request,
        DuplicatesIndexInterface $duplicatesIndex,
        DefaultCustomerDuplicatesView $duplicatesView
    ) {
        // fetch all filters
        $filters = $request->get('filter', []);
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
            $this->getSearchHelper()->addListingFilters($customerList, $filters, $this->getAdminUser());
        }

        $paginator = $duplicatesIndex->getPotentialDuplicates(
            $request->get('page', 1),
            50,
            $request->get('declined'),
            $customerList
        );

        return $this->render(
            '@PimcoreCustomerManagementFramework/admin/duplicates/list.html.twig',
            [
                'paginator' => $paginator,
                'paginationVariables' => $paginator->getPaginationData(),
                'duplicates' => $paginator->getItems(),
                'duplicatesView' => $duplicatesView,
                'searchBarFields' => $this->getSearchHelper()->getConfiguredSearchBarFields(),
                'filters' => $filters,
            ]
        );
    }

    /**
     * @param Request $request
     * @Route("/decline/{id}")
     *
     * @return JsonResponse
     */
    public function declineAction(Request $request, DuplicatesIndexInterface $duplicatesIndex)
    {
        try {
            $duplicatesIndex->declinePotentialDuplicate(
                $request->get('id')
            );

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @return SearchHelper
     */
    protected function getSearchHelper()
    {
        return $this->searchHelper;
    }

    #[Required]
    public function setSearchHelper(SearchHelper $searchHelper)
    {
        return $this->searchHelper = $searchHelper;
    }
}
