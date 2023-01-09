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

namespace CustomerManagementFrameworkBundle\Controller\Admin\Customers;

use CustomerManagementFrameworkBundle\ActivityView\DefaultActivityView;
use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customers/filter-definition")
 */
class FilterDefinitionController extends Admin
{
    /**
     * @param Request $request
     * @Route("/delete", name="cmf_filter_definition_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, DefaultActivityView $defaultActivityView)
    {
        // fetch object parameters from request
        $id = $this->getIdFromRequest($request);
        // check if FilterDefinition id provided
        if (empty($id)) {
            return $this->getRedirectToFilter();
        }
        // fetch FilterDefinition by id
        $filterDefinition = FilterDefinition::getById($id);
        // check if FilterDefinition found
        if (!$filterDefinition instanceof FilterDefinition) {
            return $this->getRedirectToFilter();
        }
        // check if user is allowed to change FilterDefinition object (must be owner or filter admin)
        if (!$filterDefinition->isUserAllowedToUpdate($this->getAdminUser())) {
            // add error message for user not allowed to access FilterDefinition object
            $errors[] = $defaultActivityView->translate('cmf_filter_definition_errors_access');

            return $this->getRedirectToFilter($filterDefinition->getId());
        }
        // try to delete the FilterDefinition
        try {
            $filterDefinition->delete();
        } catch (\Exception $e) {
            // add error message for deletion failed
            $errors[] = $defaultActivityView->translate('cmf_filter_definition_errors_deletion_failed', $e->getMessage());
        }

        // redirect to filter view with no FilterDefinition selected
        return $this->getRedirectToFilter();
    }

    /**
     * Save new FilterDefinition object
     *
     * @Route("/save", name="cmf_filter_definition_save")
     *
     * @param Request $request
     * @param CustomerViewInterface $customerView
     *
     * @return RedirectResponse
     */
    public function saveAction(Request $request, CustomerViewInterface $customerView)
    {
        // fetch object parameters from request
        $filterDefinition = $this->getFilterDefinitionFromRequest($request, true);
        // check mandatory FilterDefinition name
        if (empty($filterDefinition->getName())) {
            // add error message for missing filter name
            $errors[] = $customerView->translate('cmf_filter_definition_errors_name_missing');

            return $this->getRedirectToFilter(0, $errors);
        }
        try {
            $filterDefinition->save();
        } catch (\Exception $e) {
            // add error message for failed save
            $errors[] = $customerView->translate('cmf_filter_definition_errors_save_failed', $e->getMessage());

            return $this->getRedirectToFilter(0, $errors);
        }
        // redirect to filter view with new FilterDefinition selected
        return $this->getRedirectToFilter($filterDefinition->getId());
    }

    /**
     * Update existing FilterDefinition object
     *
     * @Route("/update", name="cmf_filter_definition_update")
     *
     * @param Request $request
     * @param CustomerViewInterface $customerView
     *
     * @return RedirectResponse
     */
    public function updateAction(Request $request, CustomerViewInterface $customerView)
    {
        // fetch object parameters from request
        $filterDefinition = $this->getFilterDefinitionFromRequest($request, true, true);
        // check mandatory FilterDefinition name
        if (empty($filterDefinition->getName())) {
            // add error message for missing filter name
            $errors[] = $customerView->translate('cmf_filter_definition_errors_name_missing');

            return $this->getRedirectToFilter(0, $errors);
        }
        // check if user is allowed to update object
        if (!$filterDefinition->isUserAllowedToUpdate($this->getAdminUser())) {
            // add error message for user not allowed to access FilterDefinition object
            $errors[] = $customerView->translate('cmf_filter_definition_errors_change');

            return $this->getRedirectToFilter(0, $errors);
        }
        try {
            $filterDefinition->save();
        } catch (\Exception $e) {
            // add error message for failed save
            $errors[] = $customerView->translate('cmf_filter_definition_errors_save_failed', $e->getMessage());

            return $this->getRedirectToFilter(0, $errors);
        }
        // redirect to filter view with new FilterDefinition selected
        return $this->getRedirectToFilter($filterDefinition->getId());
    }

    /**
     * Share the filter definition with new users or roles. Customer view admins will use updateFilterDefinition.
     * This action is only used by users which are in allowed users for FilterDefinition object.
     *
     * @Route("/share", name="cmf_filter_definition_share")
     *
     * @param Request $request
     * @param CustomerViewInterface $customerView
     *
     * @return bool|RedirectResponse
     */
    public function shareAction(Request $request, CustomerViewInterface $customerView)
    {
        // fetch object parameters from request
        $filterDefinition = $this->getFilterDefinitionFromRequest($request, false, true);
        // check if FilterDefinition id provided
        if (!$filterDefinition instanceof FilterDefinition || empty($this->getAllowedUserIdsFromRequest($request))) {
            return $this->getRedirectToFilter();
        }
        // initialize error array
        $errors = [];
        // check if user is allowed to share FilterDefinition object
        if (!$filterDefinition->isUserAllowedToShare($this->getAdminUser())) {
            // add error message for user not allowed to access FilterDefinition object
            $errors[] = $customerView->translate('cmf_filter_definition_errors_access.');

            return $this->getRedirectToFilter(0, $errors);
        }
        // try to update the FilterDefinition
        try {
            // add new allowed user ids
            $filterDefinition->addAllowedUserIds($this->getAllowedUserIdsFromRequest($request));
            // save changes to FilterDefinition object
            $filterDefinition->save();
        } catch (\Exception $e) {
            // add error message for deletion failed
            $errors[] = $customerView->translate('cmf_filter_definition_errors_share_failed', $e->getMessage());

            return $this->getRedirectToFilter();
        }
        // redirect to filter view with new FilterDefinition selected
        return $this->getRedirectToFilter($filterDefinition->getId());
    }

    /**
     * Create redirect to customer view with selected filter
     *
     * @param int $filterDefinitionId
     * @param array $errors
     *
     * @return RedirectResponse
     */
    protected function getRedirectToFilter(int $filterDefinitionId = 0, array $errors = [])
    {
        // redirect to filter view with new FilterDefinition selected
        return $this->redirect($this->generateUrl('customermanagementframework_admin_customers_list', [
            'filterDefinition' => ['id' => $filterDefinitionId],
            'errors' => $errors,
        ]));
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    protected function getIdFromRequest(Request $request)
    {
        return intval($request->get('filterDefinition', [])['id'] ?? 0);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getNameFromRequest(Request $request)
    {
        return strval($request->get('filterDefinition', [])['name'] ?? '');
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function getDefinitionFromRequest(Request $request)
    {
        return $request->get('filter', []);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getShowSegmentsFromRequest(Request $request)
    {
        return $this->getDefinitionFromRequest($request)['showSegments'] ?? [];
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function getReadOnlyFromRequest(Request $request)
    {
        return boolval($request->get('filterDefinition', [])['readOnly'] ?? false);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function getShortcutAvailableFromRequest(Request $request)
    {
        return boolval($request->get('filterDefinition', [])['shortcutAvailable'] ?? false);
    }

    /**
     * Prepare allowed user ids from request. This parameter consists of allowedUserIds and allowedRoleIds
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getAllowedUserIdsFromRequest(Request $request)
    {
        $allowedUserIds = $request->get('filterDefinition', [])['allowedUserIds'] ?? [];
        $allowedRoleIds = $request->get('filterDefinition', [])['allowedRoleIds'] ?? [];
        $preparedAllowedUserIds = array_unique(array_merge($allowedUserIds, $allowedRoleIds));
        sort($preparedAllowedUserIds);

        return $preparedAllowedUserIds;
    }

    /**
     * Create FilterDefinition objects with parameters set from request
     *
     * @param Request $request
     * @param bool $setParametersFromRequest Flag if all parameters should be set from request parameters
     * @param bool $loadById True means load FilterDefinition by id provided in request. False means creating a new FilterDefinition object
     *
     * @return FilterDefinition|null Null will be returned if loadById set and no id provided or object with id not found
     */
    protected function getFilterDefinitionFromRequest(Request $request, bool $setParametersFromRequest = false, bool $loadById = false)
    {
        // fetch object parameters from request
        $id = $this->getIdFromRequest($request);
        // check mandatory FilterDefinition name
        if ($loadById) {
            // check if id exists
            if (empty($id)) {
                return null;
            }
            // try to load FilterDefinition by id
            $filterDefinition = FilterDefinition::getById($id);
            // check if FilterDefinition found
            if (!$filterDefinition instanceof FilterDefinition) {
                return null;
            }
        } else {
            // create new filter definition from scratch
            $filterDefinition = new FilterDefinition();
        }
        if ($setParametersFromRequest) {
            // set parameters
            $filterDefinition->setName($this->getNameFromRequest($request));
            $filterDefinition->setDefinition($this->getDefinitionFromRequest($request));
            $filterDefinition->setAllowedUserIds($this->getAllowedUserIdsFromRequest($request));
            $filterDefinition->setShowSegments($this->getShowSegmentsFromRequest($request));
            $filterDefinition->setReadOnly($this->getReadOnlyFromRequest($request));
            $filterDefinition->setShortcutAvailable($this->getShortcutAvailableFromRequest($request));
            // set owner only for new FilterDefinition object
            if (!$loadById) {
                $filterDefinition->setOwnerId($this->getAdminUser()->getId());
            }
        }

        return $filterDefinition;
    }
}
