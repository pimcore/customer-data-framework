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
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\CustomerList\Exporter\AbstractExporter;
use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use CustomerManagementFrameworkBundle\CustomerList\ExporterManagerInterface;
use CustomerManagementFrameworkBundle\CustomerList\Filter\CustomerSegment as CustomerSegmentFilter;
use CustomerManagementFrameworkBundle\CustomerList\Filter\Exception\SearchQueryException;
use CustomerManagementFrameworkBundle\CustomerList\Filter\SearchQuery;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Listing\Filter;
use CustomerManagementFrameworkBundle\Listing\FilterHandler;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use function GuzzleHttp\Psr7\str;
use Pimcore\Db;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ValidatorBuilder;
use Zend\Paginator\Adapter\ArrayAdapter;

/**
 * @Route("/customers")
 */
class CustomersController extends Admin
{
    // params still needed when clearing all filters
    protected $clearUrlParams = [];

    /**
     * @var CustomerSegmentGroup[]|null
     */
    private $segmentGroups = null;

    public function onKernelController(FilterControllerEvent $event)
    {
        parent::onKernelController($event);
        $this->checkPermission('plugin_cmf_perm_customerview');

        \Pimcore\Model\DataObject\AbstractObject::setHideUnpublished(true);
    }

    /**
     * @param Request $request
     * @Route("/list", name="cmf_customer_list")
     * @return Response
     */
    public function listAction(Request $request)
    {
        $filters = $this->fetchListFilters($request);
        $errors = [];
        $paginator = null;
        $customerView = \Pimcore::getContainer()->get('cmf.customer_view');

        // when filter share button was clicked
        if ($request->get('share-filter-definition')) {
            $return = $this->shareFilterDefinition($request, $errors);
            // no errors return and select new filter
            if ($return instanceof RedirectResponse) {
                return $return;
            }
        }

        try {
            $listing = $this->buildListing($filters);
            $paginator = $this->buildPaginator($request, $listing);
        } catch (SearchQueryException $e) {
            $errors[] = $customerView->translate('There was an error in you search query: %s', $e->getMessage());
        } catch (\Exception $e) {
            $errors[] = $customerView->translate('Error while building customer list: %s', $e->getMessage());
        }

        //empty paginator as the view expects a valid paginator
        if (null === $paginator) {
            $paginator = $this->buildPaginator($request, new ArrayAdapter([]));
        }

        return $this->render(
            'PimcoreCustomerManagementFrameworkBundle:Admin\Customers:list.html.php',
            [
                'segmentGroups' => $this->loadSegmentGroups(),
                'filters' => $filters,
                'errors' => $errors,
                'paginator' => $paginator,
                'customerView' => $customerView,
                'searchBarFields' => $this->getConfiguredSearchBarFields(),
                'request' => $request,
                'clearUrlParams' => $this->clearUrlParams,
                'filterDefinitions' => $this->getFilterDefinitions(),
                'filterDefinition' => $this->getFilterDefinition($request),
            ]
        );
    }

    /**
     * @param Request $request
     * @Route("/detail")
     * @return Response
     */
    public function detailAction(Request $request)
    {
        $customer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById((int)$request->get('id'));
        if ($customer && $customer instanceof CustomerInterface) {
            $customerView = \Pimcore::getContainer()->get('cmf.customer_view');
            if (!$customerView->hasDetailView($customer)) {
                throw new \RuntimeException(sprintf('Customer %d has no detail view to show', $customer->getId()));
            }

            return $this->render(
                'PimcoreCustomerManagementFrameworkBundle:Admin\Customers:detail.html.php',
                [
                    'customer' => $customer,
                    'customerView' => $customerView,
                    'request' => $request,
                ]
            );
        } else {
            throw new \InvalidArgumentException('Invalid customer');
        }
    }

    /**
     * @param Request $request
     * @Route("/export")
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function exportAction(Request $request)
    {
        $filters = $this->fetchListFilters($request);
        $listing = $this->buildListing($filters);

        $query = $listing->getQuery();
        $query->reset(QueryBuilder::COLUMNS);
        $query->columns(['o_id']);
        $ids = Db::get()->fetchCol($query);

        $jobId = uniqid();
        \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->saveExportTmpData($jobId, [
            'processIds' => $ids,
            'exporter' => $request->get('exporter'),
        ]);

        return $this->json([
            'url' => $this->generateUrl('customermanagementframework_admin_customers_exportstep', ['jobId' => $jobId]),
            'jobId' => $jobId,
            'exporter' => $request->get('exporter'),
        ]);
    }

    /**
     * @param Request $request
     * @route("/export-step")
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function exportStepAction(Request $request)
    {
        $perRequest = $request->get('perRequest',
            \Pimcore::getContainer()->getParameter('cmf.customer_export.items_per_request'));

        try {
            $data = \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->getExportTmpData($request);
        } catch (\Exception $e) {
            return $this->json([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        //export finished
        if (!sizeof($data['processIds'])) {
            return $this->json([
                'finished' => true,
                'url' => $this->generateUrl('customermanagementframework_admin_customers_downloadfinishedexport',
                    ['jobId' => $request->get('jobId')]),
                'jobId' => $request->get('jobId'),
            ]);
        }

        $ids = array_slice($data['processIds'], 0, $perRequest);
        $processIds = array_slice($data['processIds'], $perRequest);

        $listing = $this->buildListing();
        $listing->addConditionParam('o_id in ('.implode(', ', $ids).')');

        $exporter = $this->getExporter($listing, $data['exporter']);
        $exportData = $exporter->getExportData();

        $totalExportData = isset($data['exportData']) ? $data['exportData'] : [];
        $totalExportData = array_merge_recursive($totalExportData, $exportData);

        $data['exportData'] = $totalExportData;
        $data['processIds'] = $processIds;

        \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->saveExportTmpData($request->get('jobId'),
            $data);

        $notProcessedRecordsCount = sizeof($data['processIds']);
        $totalRecordsCount = $notProcessedRecordsCount + sizeof($data['exportData'][AbstractExporter::ROWS]);

        $percent = round(($totalRecordsCount - $notProcessedRecordsCount) * 100 / $totalRecordsCount, 0);

        return $this->json([
            'finished' => false,
            'jobId' => $request->get('jobId'),
            'notProcessedRecordsCount' => $notProcessedRecordsCount,
            'totalRecordsCount' => $totalRecordsCount,
            'percent' => $percent,
            'progress' => sprintf('%s/%s (%s %%)', ($totalRecordsCount - $notProcessedRecordsCount), $totalRecordsCount,
                $percent),

        ]);
    }

    /**
     * @param Request $request
     * @route("/download-finished-export")
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse|Response
     */
    public function downloadFinishedExportAction(Request $request)
    {
        try {
            $data = \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->getExportTmpData($request);
        } catch (\Exception $e) {
            return $this->json([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        if (sizeof($data['processIds'])) {
            return $this->json([
                'error' => true,
                'message' => 'export not finished yet',
            ]);
        }

        $exportData = $data['exportData'];

        $listing = $this->buildListing();
        $exporter = $this->getExporter($listing, $data['exporter']);

        $filename = sprintf(
            '%s-%s-segment-export.%s',
            $exporter->getName(),
            \Carbon\Carbon::now()->format('YmdHis'),
            $exporter->getExtension()
        );

        $response = new Response();
        $response
            ->setContent($exporter->generateExportFile($exportData))
            ->headers->add(
                [
                    'Content-Type' => $exporter->getMimeType(),
                    'Content-Length' => $exporter->getFilesize(),
                    'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
                ]
            );

        \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->deleteExportTmpData($request->get('jobId'));

        return $response;
    }

    /**
     * @param Listing\Concrete $listing
     * @param $exporterName
     * @return ExporterInterface
     * @internal param Request $request
     *
     */
    protected function getExporter(Listing\Concrete $listing, $exporterName)
    {
        /**
         * @var ExporterManagerInterface $exporterManager
         */
        $exporterManager = \Pimcore::getContainer()->get('cmf.customer_exporter_manager');

        if (!$exporterManager->hasExporter($exporterName)) {
            throw new \InvalidArgumentException('Exporter does not exist');
        }

        return $exporterManager->buildExporter($exporterName, $listing);
    }

    /**
     * Load all segment groups
     *
     * @return CustomerSegmentGroup[]
     */
    protected function loadSegmentGroups()
    {
        if (is_null($this->segmentGroups)) {
            /** @var CustomerSegmentGroup\Listing $segmentGroups */
            $segmentGroups = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroups();
            $segmentGroups->addConditionParam('showAsFilter = 1');
            // sort by filterSortOrder high to low
            $segmentGroups->setOrderKey('filterSortOrder IS NULL, filterSortOrder DESC', false);
            $this->segmentGroups = $segmentGroups->load();
        }

        return $this->segmentGroups;
    }

    /**
     * @param array $filters
     * @return Listing\Concrete
     */
    protected function buildListing(array $filters = [])
    {
        $listing = \Pimcore::getContainer()->get('cmf.customer_provider')->getList();
        $listing
            ->setOrderKey('o_id')
            ->setOrder('ASC');

        $this->addListingFilters($listing, $filters);

        return $listing;
    }

    /**
     * @param Listing\Concrete $listing
     * @param array $filters
     * @throws \Exception
     */
    protected function addListingFilters(Listing\Concrete $listing, array $filters = [])
    {
        $handler = new FilterHandler($listing);

        $filterProperties = \Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.customer_list.filter_properties');

        $equalsProperties = isset($filterProperties['equals']) ? $filterProperties['equals'] : [];
        $searchProperties = isset($filterProperties['search']) ? $filterProperties['search'] : [];

        foreach ($equalsProperties as $property => $databaseField) {
            if (array_key_exists($property, $filters)) {
                $handler->addFilter(new Filter\Equals($databaseField, $filters[$property]));
            }
        }

        foreach ($searchProperties as $property => $databaseFields) {
            if (array_key_exists($property, $filters) && !empty($filters[$property]) && is_string(
                    $filters[$property]
                )
            ) {
                $handler->addFilter(new SearchQuery($databaseFields, $filters[$property]));
            }
        }

        if (array_key_exists('segments', $filters)) {
            foreach ($filters['segments'] as $groupId => $segmentIds) {
                $segmentGroup = null;
                if ($groupId !== 'default') {
                    /** @var \Pimcore\Model\DataObject\CustomerSegmentGroup $segmentGroup */
                    $segmentGroup = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroupById($groupId);
                    if (!$segmentGroup) {
                        throw new \Exception(sprintf('Segment group %d was not found', $groupId));
                    }
                }

                $segments = [];
                foreach ($segmentIds as $segmentId) {
                    $segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentById($segmentId);

                    if (!$segment) {
                        throw new \Exception(sprintf('Segment %d was not found', $segmentId));
                    }

                    $segments[] = $segment;
                }

                $handler->addFilter(new CustomerSegmentFilter($segments, $segmentGroup));
            }
        }
    }

    /**
     * Fetch filters and set them on view
     *
     * @param Request $request
     *
     * @return array
     */
    protected function fetchListFilters(Request $request)
    {
        $filters = $request->get('filter', []);
        $filters = $this->addPrefilteredSegmentToFilters($request, $filters);
        $filters = $this->addFilterDefinitionCustomer($request, $filters);

        return $filters;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    protected function addPrefilteredSegmentToFilters(Request $request, array $filters)
    {
        $segment = $this->fetchPrefilteredSegment($request);
        if ($segment) {
            if (!isset($filters['segments'])) {
                $filters['segments'] = [];
            }

            $groupId = $segment->getGroup() ? $segment->getGroup()->getId() : 'default';

            $groupSegmentIds = [];
            if (isset($filters['segments'][$groupId])) {
                $groupSegmentIds = $filters['segments'][$groupId];
            }

            if (!in_array($segment->getId(), $groupSegmentIds)) {
                $groupSegmentIds[] = $segment->getId();
            }

            $filters['segments'][$groupId] = $groupSegmentIds;
        }

        $filters = $this->addFilterDefinitionSegments($request, $filters);

        return $filters;
    }

    /**
     * @param Request $request
     *
     * @return CustomerSegmentInterface|null
     */
    protected function fetchPrefilteredSegment(Request $request)
    {
        $segmentId = $request->get('segmentId');

        if ($segmentId) {
            $segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentById($segmentId);
            if (!$segment) {
                throw new \InvalidArgumentException(sprintf('Segment %d was not found', $segmentId));
            }

            $this->clearUrlParams['segmentId'] = $segment->getId();

            return $segment;
        }
    }

    /**
     * @return array
     */
    protected function getConfiguredSearchBarFields()
    {
        $filterProperties = \Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.customer_list.filter_properties');
        $searchProperties = $filterProperties['search'];

        $searchBarFields = [];
        if (isset($searchProperties['search'])) {
            $searchProperties = $searchProperties['search'];

            if (is_array($searchProperties) && count($searchProperties) > 0) {
                $searchBarFields = array_values($searchProperties);
            }
        }

        return $searchBarFields;
    }

    /**
     * Fetch all filter definitions available for current user
     *
     * @return \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition[]
     */
    protected function getFilterDefinitions()
    {
        // load filter definitions
        $FilterDefinitionListing = new FilterDefinition\Listing();
        if (!$this->getUser()->isAllowed('plugin_cmf_perm_customerview_admin')) {
            // build user ids condition for filter definition
            $FilterDefinitionListing->setUserIdsCondition($this->getUserIds());
        }

        // return loaded filter definitions array
        return $FilterDefinitionListing->load();
    }

    /**
     * Fetch the definition of a FilterDefinition object
     *
     * @param Request $request
     * @return null|FilterDefinition Returns FilterDefinition object if definition key is defined in filters array,
     * FilterDefinition with id in DB and user is allowed to use FilterDefinition. Otherwise returns null.
     */
    protected function getFilterDefinition(Request $request)
    {
        // fetch filter definition information
        $filterDefinitionData = $request->get('filterDefinition', []);
        // build default FilterDefinition object if no selected
        $DefaultFilterDefinition = (new FilterDefinition())->setShowSegments(Objects::getIdsFromArray($this->loadSegmentGroups()));
        // check if filter definition given
        if (!array_key_exists('id', $filterDefinitionData)) {
            // no filter definition found
            return $DefaultFilterDefinition;
        }
        // check if filter definition object exists
        $filterDefinition = FilterDefinition::getById((int)$filterDefinitionData['id']);
        if (!$filterDefinition instanceof FilterDefinition) {
            // no filter definition available
            return $DefaultFilterDefinition;
        }
        // check if current user is allowed to use FilterDefinition
        if (!$filterDefinition->isAnyUserAllowed($this->getUserIds()) && !$this->getUser()->isAllowed('plugin_cmf_perm_customerview_admin')) {
            // user is not allowed to use FilterDefinition
            return $DefaultFilterDefinition;
        }

        // return FilterDefinition definition
        return $filterDefinition;
    }

    /**
     * Fetch all user ids of current user
     *
     * @return array
     */
    protected function getUserIds()
    {
        // fetch roles of user
        $userIds = $this->getUser()->getRoles();
        // fetch id of user
        $userIds[] = $this->getUser()->getId();

        // return user ids
        return $userIds;
    }

    /**
     * Merge FilterDefinition for customer fields
     *
     * @param Request $request
     * @param array $filters
     * @return array
     */
    protected function addFilterDefinitionCustomer(Request $request, array $filters)
    {
        // merge filters with filters of filter definition
        $filterDefinition = $this->getFilterDefinition($request);

        // check if filter definitions found
        if (is_null($filterDefinition)) {
            return $filters;
        }

        // fetch definitions for customer / root without segments
        $filterDefinitionCustomer = $filterDefinition->getDefinition();
        unset($filterDefinitionCustomer['segments']);

        if ($filterDefinition->isReadOnly()) {
            // overwrite filters with FilterDefinition definition
            $filters = array_merge($filters, $filterDefinitionCustomer);
            // lock read only filters
            foreach ($filterDefinitionCustomer as $key => $value) {
                /** @noinspection PhpUndefinedFieldInspection */
                $this->readonlyFilterFields[] = $key;
            }
        } else {
            // filter of user more important than filter definition
            $filters = array_merge($filterDefinitionCustomer, $filters);
        }

        // return merged filters array
        return $filters;
    }

    /**
     * Add segment filters from FilterDefinition
     *
     * @param Request $request
     * @param array $filters
     * @return array
     */
    protected function addFilterDefinitionSegments(Request $request, array $filters)
    {
        $filters['showSegments'] = $filters['showSegments'] ?? [];

        // merge filters with filters of filter definition
        $filterDefinition = $this->getFilterDefinition($request);

        // check if filter definitions found
        if (is_null($filterDefinition)) {
            return $filters;
        }

        // fetch definitions for segments / only segments array
        $filterDefinition->cleanUp(false);
        $filterDefinitionSegments = @$filterDefinition->getDefinition()['segments'] ?: [];

        if ($filterDefinition->isReadOnly()) {
            // overwrite filters with FilterDefinition definition
            $filters['segments'] = $filterDefinitionSegments;
        } else {
            // filter of user more important than filter definition
            $filters['segments'] = array_replace_recursive($filterDefinitionSegments, @$filters['segments'] ?: []);
        }

        // set to filter which segments to show
        $filters['showSegments'] = $request->get('apply-segment-selection') ? $filters['showSegments'] : $filterDefinition->getShowSegments();

        // return merged filters array
        return $filters;
    }

    /**
     * Share the filter definition with new users or roles. Customer view admins will use updateFilterDefinition.
     *
     * @param Request $request
     * @param array $errors
     * @return bool|RedirectResponse
     */
    protected function shareFilterDefinition(Request $request, array &$errors) {
        // fetch CustomerView object
        $customerView = \Pimcore::getContainer()->get('cmf.customer_view');
        // fetch object parameters from request
        $id = intval($request->get('filterDefinition', [])['id'] ?? 0);
        $newAllowedUserIds = $this->getAllowedUserIdsFromRequest($request);
        // check if FilterDefinition id provided
        if (empty($id) || empty($newAllowedUserIds)) {
            return false;
        }
        // fetch FilterDefinition by id
        $filterDefinition = FilterDefinition::getById($id);
        // check if FilterDefinition found
        if (!$filterDefinition instanceof FilterDefinition) {
            return false;
        }
        // check if user is allowed to access FilterDefinition object
        if (!$filterDefinition->isUserAllowed($this->getUser()->getId()) && !$this->getUser()->isAllowed('plugin_cmf_perm_customerview_admin')) {
            // add error message for user not allowed to access FilterDefinition object
            $errors[] = $customerView->translate('Not allowed to access filter.');
            return false;
        }
        // try to update the FilterDefinition
        try {
            // add new allowed user ids
            $filterDefinition->addAllowedUserIds($newAllowedUserIds);
            // save changes to FilterDefinition object
            $filterDefinition->save();
        } catch (\Exception $e) {
            // add error message for deletion failed
            $errors[] = $customerView->translate('Sharing of filter failed. ' . $e->getMessage());
            return false;
        }

        // redirect to filter view with new FilterDefinition selected
        return $this->redirect($this->generateUrl($request->get('_route'), [
            'filterDefinition' =>
                [
                    'id' => $filterDefinition->getId(),
                ],
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
