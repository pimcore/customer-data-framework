<?php
/**
 * Created by PhpStorm.
 * User: dschroffner
 * Date: 04.12.2017
 * Time: 15:03
 */


namespace CustomerManagementFrameworkBundle\Controller\Admin\Customers;

use CustomerManagementFrameworkBundle\Controller\Admin;
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
     * @Route("/test")
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function testAction(Request $request)
    {
        /*
        $item = new FilterDefinition;
        $item->setName('First Filter');
        $item->setDefinition([
            'name' => 'test',
            'email' => 'test@test.at',
            'segments' => [
                6692924 => [6697161]
            ],
        ]);
        $item->setAllowedUserIds([2,3,5,7,9,11,13,17]);
        $item->setShowSegments([6692924,6692950,6694248,6697154,6697869]);
        $item->setReadOnly(true);
        $item->setShortcutAvailable(true);
        $item->save();

        $item2 = new FilterDefinition;
        $item2->setName('Second Filter');
        $item2->setDefinition([
            'name' => 'test2',
            'email' => 'test2@test2.at',
        ]);
        $item2->setAllowedUserIds([11,13,17]);
        $item2->setShowSegments([6692924,6692950,6694248]);
        $item2->setReadOnly(false);
        $item2->setShortcutAvailable(false);
        $item2->save();

        return $this->json([
            'id' => $item->getId(),
            'id2' => $item2->getId(),
        ]);
        */

        /*
        $listing = new FilterDefinition\Listing();

        if(!$this->getUser()->isAllowed('plugin_cmf_perm_customerview_admin')) {
            var_dump("filtering");
            die;
            // fetch roles of user
            $userIds = $this->getUser()->getRoles();
            // fetch id of user
            $userIds[] = $this->getUser()->getId();
            // build user ids condition for filter definition
            $listing->setUserIdsCondition($userIds);
        }

        foreach($listing->load() as $item) {
            var_dump($item->getName());
            var_dump($item->getShowSegments());
        }
        die();

        //return $this->json([]);
        */


        die;
    }

    /**
     * @param Request $request
     * @Route("/delete", name="cmf_filter_definition_delete")
     * @return RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        // fetch CustomerView object
        $customerView = \Pimcore::getContainer()->get('cmf.customer_view');
        // fetch object parameters from request
        $id = intval($request->get('filterDefinition', [])['id'] ?? 0);
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
        // check if user is allowed to access FilterDefinition object
        if (!$this->getUser()->isAllowed('plugin_cmf_perm_customerview_admin')) {
            // add error message for user not allowed to access FilterDefinition object
            $errors[] = $customerView->translate('Not allowed to access filter.');
            return $this->getRedirectToFilter($filterDefinition->getId());
        }
        // try to delete the FilterDefinition
        try {
            $filterDefinition->delete();
        } catch (\Exception $e) {
            // add error message for deletion failed
            $errors[] = $customerView->translate('Deletion of filter failed. '.$e->getMessage());
        }
        // redirect to filter view with no FilterDefinition selected
        return $this->getRedirectToFilter();
    }

    /**
     * @Route("/save", name="cmf_filter_definition_save")
     * @param Request $request
     */
    public function saveAction(Request $request) {
        // initialize errors array
        $errors = [];
        // fetch CustomerView object
        $customerView = \Pimcore::getContainer()->get('cmf.customer_view');

        // fetch object parameters from request
        $name = strval($request->get('filterDefinition', [])['name'] ?? '');
        $definition = $request->get('filter', []);
        $allowedUserIds = $this->getAllowedUserIdsFromRequest($request);
        $showSegments = ($request->get('filter', [])['showSegments'] ?? []);
        $readOnly = boolval($request->get('filterDefinition', [])['readOnly'] ?? false);
        $shortcutAvailable = boolval($request->get('filterDefinition', [])['shortcutAvailable'] ?? false);

        // check mandatory FilterDefinition name
        if (empty($name)) {
            // add error messsage for missing filter name
            $errors[] = $customerView->translate('Please provide a filter name.');
            return $this->getRedirectToFilter(0, $errors);
        }

        $filterDefinition = new FilterDefinition();
        $filterDefinition->setName($name);
        $filterDefinition->setDefinition($definition);
        $filterDefinition->setAllowedUserIds($allowedUserIds);
        $filterDefinition->setShowSegments($showSegments);
        $filterDefinition->setReadOnly($readOnly);
        $filterDefinition->setShortcutAvailable($shortcutAvailable);

        try {
            $filterDefinition->save();
        } catch (\Exception $e) {
            // add error message for failed save
            $errors[] = $customerView->translate('Save of filter failed. '.$e->getMessage());
            return $this->getRedirectToFilter(0, $errors);
        }

        // redirect to filter view with new FilterDefinition selected
        return $this->redirect($this->generateUrl('cmf_customer_list', [
            'filterDefinition' =>
                [
                    'id' => $filterDefinition->getId(),
                ],
        ]));
    }

    // TODO add share action from CustomersController

    /**
     * Create redirect to customer view with selected filter
     *
     * @param int $filterDefinitionId
     * @param array $errors
     * @return RedirectResponse
     */
    protected function getRedirectToFilter(int $filterDefinitionId = 0, array $errors  = []) {
        // redirect to filter view with new FilterDefinition selected
        return $this->redirect($this->generateUrl('cmf_customer_list', [
            'filterDefinition' =>
                [
                    'id' => $filterDefinitionId,
                ],
            'errors' => $errors
        ]));
    }

    /**
     * Prepare allowed user ids from request. This parameter consists of allowedUserIds and allowedRoleIds
     *
     * @param Request $request
     * @return array
     */
    protected function getAllowedUserIdsFromRequest(Request $request) {
        $allowedUserIds = $request->get('filterDefinition', [])['allowedUserIds'] ?? [];
        $allowedRoleIds = $request->get('filterDefinition', [])['allowedRoleIds'] ?? [];
        $preparedAllowedUserIds =  array_unique(array_merge($allowedUserIds, $allowedRoleIds));
        sort($preparedAllowedUserIds);
        return $preparedAllowedUserIds;
    }
}