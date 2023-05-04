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

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\CustomerList\Exporter\AbstractExporter;
use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use CustomerManagementFrameworkBundle\CustomerList\ExporterManagerInterface;
use CustomerManagementFrameworkBundle\CustomerList\Filter\Exception\SearchQueryException;
use CustomerManagementFrameworkBundle\CustomerList\SearchHelper;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Pimcore\Db;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\Element\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customers")
 */
class CustomersController extends Admin
{
    /**
     * @var CustomerSegmentGroup[]|null
     */
    private $segmentGroups = null;

    private ExporterManagerInterface $exporterManager;

    public function onKernelControllerEvent(ControllerEvent $event): void
    {
        parent::onKernelControllerEvent($event);
        $this->checkPermission('plugin_cmf_perm_customerview');
        AbstractObject::setHideUnpublished(true);
    }

    /**
     * @required
     */
    public function setExporterManager(ExporterManagerInterface $exporterManager): void
    {
        $this->exporterManager = $exporterManager;
    }

    /**
     * @Route("/list")
     */
    public function listAction(Request $request): Response
    {
        $filters = $this->fetchListFilters($request);
        $orders = $this->fetchListOrder($request);
        $errors = $request->get('errors', []);
        $paginator = null;
        $customerView = \Pimcore::getContainer()->get('cmf.customer_view');

        try {
            $listing = $this->buildListing($filters, $orders);
            $paginator = $this->buildPaginator($request, $listing);
        } catch (SearchQueryException $e) {
            $errors[] = $customerView->translate('There was an error in you search query: %s', $e->getMessage());
        } catch (\Exception $e) {
            $errors[] = $customerView->translate('Error while building customer list: %s', $e->getMessage());
        }

        //empty paginator as the view expects a valid paginator
        if (null === $paginator) {
            $paginator = $this->buildPaginator($request, []);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render($customerView->getOverviewWrapperTemplate(), [
                'paginator' => $paginator,
                'paginationVariables' => $paginator->getPaginationData(),
                'customerView' => $customerView,
                'idField' => Service::getVersionDependentDatabaseColumnName('id')
            ]);
        } else {
            return $this->render(
                '@PimcoreCustomerManagementFramework/admin/customers/list.html.twig',
                [
                    'segmentGroups' => $this->loadSegmentGroups(),
                    'filters' => $filters,
                    'errors' => $errors,
                    'paginator' => $paginator,
                    'paginationVariables' => $paginator->getPaginationData(),
                    'customerView' => $customerView,
                    'searchBarFields' => $this->getSearchHelper()->getConfiguredSearchBarFields(),
                    'request' => $request,
                    'filterDefinitions' => $this->getFilterDefinitions(),
                    'filterDefinition' => $this->getFilterDefinition($request),
                    'accessToTempCustomerFolder' => boolval($this->hasUserAccessToTempCustomerFolder()),
                    'hideAdvancedFilterSettings' => boolval($request->get('segmentId')),
                    'idField' => Service::getVersionDependentDatabaseColumnName('id')
                ]
            );
        }
    }

    /**
     * @Route("/detail")
     */
    public function detailAction(Request $request): Response
    {
        $customer = $this->getSearchHelper()->getCustomerProvider()->getById((int)$request->get('id'));
        if ($customer instanceof CustomerInterface) {
            $customerView = \Pimcore::getContainer()->get('cmf.customer_view');
            if (!$customerView->hasDetailView($customer)) {
                throw new \RuntimeException(sprintf('Customer %d has no detail view to show', $customer->getId()));
            }

            /**
             * @var Concrete $customer
             */
            if (!$customer->isAllowed('view')) {
                throw new \RuntimeException(sprintf('Not allowed to view customer %d', $customer->getId()));
            }

            return $this->render(
                '@PimcoreCustomerManagementFramework/admin/customers/detail.html.twig',
                [
                    'customer' => $customer,
                    'customerView' => $customerView,
                    'request' => $request,
                ]
            );
        }

        throw new \InvalidArgumentException('Invalid customer');
    }

    /**
     * @Route("/export")
     */
    public function exportAction(Request $request): JsonResponse
    {
        $filters = $this->fetchListFilters($request);
        $listing = $this->buildListing($filters);

        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $query = $listing->getQueryBuilder()
            ->resetQueryPart('select')
            ->select($idField);
        $ids = Db::get()->fetchFirstColumn((string)$query);

        $jobId = uniqid();
        $this->exporterManager->saveExportTmpData($jobId, [
            'processIds' => $ids,
            'exporter' => $request->get('exporter'),
        ]);

        /** @noinspection PhpRouteMissingInspection */
        return $this->jsonResponse([
            'url' => $this->generateUrl('customermanagementframework_admin_customers_exportstep', ['jobId' => $jobId]),
            'jobId' => $jobId,
            'exporter' => $request->get('exporter'),
        ]);
    }

    /**
     * @route("/export-step")
     */
    public function exportStepAction(Request $request): JsonResponse
    {
        $perRequest = $request->get(
            'perRequest',
            $this->getParameter('cmf.customer_export.items_per_request')
        );

        try {
            $data = $this->exporterManager->getExportTmpData($request);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        //export finished
        if (!sizeof($data['processIds'])) {
            /** @noinspection PhpRouteMissingInspection */
            return $this->jsonResponse([
                'finished' => true,
                'url' => $this->generateUrl('customermanagementframework_admin_customers_downloadfinishedexport',
                    ['jobId' => $request->get('jobId')]),
                'jobId' => $request->get('jobId'),
            ]);
        }

        $ids = array_slice($data['processIds'], 0, $perRequest);
        $processIds = array_slice($data['processIds'], $perRequest);

        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $listing = $this->buildListing();
        $listing->addConditionParam($idField . ' in ('.implode(', ', $ids).')');

        $exporter = $this->getExporter($listing, $data['exporter']);
        $exportData = $exporter->getExportData();

        $totalExportData = $data['exportData'] ?? [];
        $totalExportData = array_merge_recursive($totalExportData, $exportData);

        $data['exportData'] = $totalExportData;
        $data['processIds'] = $processIds;

        $this->exporterManager->saveExportTmpData(
            $request->get('jobId'),
            $data
        );

        $notProcessedRecordsCount = sizeof($data['processIds']);
        $totalRecordsCount = $notProcessedRecordsCount + sizeof($data['exportData'][AbstractExporter::ROWS]);

        $percent = round(($totalRecordsCount - $notProcessedRecordsCount) * 100 / $totalRecordsCount);

        return $this->jsonResponse([
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
     * @route("/download-finished-export")
     */
    public function downloadFinishedExportAction(Request $request): JsonResponse | Response
    {
        try {
            $data = $this->exporterManager->getExportTmpData($request);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        if (sizeof($data['processIds'])) {
            return $this->jsonResponse([
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
            Carbon::now()->format('YmdHis'),
            $exporter->getExtension()
        );

        $content = $exporter->generateExportFile($exportData);
        $contentSize = strlen($content);

        $response = new Response();
        $response
            ->setContent($content)
            ->headers->add(
                [
                    'Content-Type' => $exporter->getMimeType(),
                    'Content-Length' => $contentSize,
                    'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
                ]
            );

        $this->exporterManager->deleteExportTmpData($request->get('jobId'));

        return $response;
    }

    /**
     * Create new customer action
     *
     * @Route("/new")
     *
     * @throws ValidationException
     */
    public function createCustomerAction(CustomerProviderInterface $customerProvider): JsonResponse
    {
        // check permissions write to temp folder -> ValidationException
        if (!$this->hasUserAccessToTempCustomerFolder()) {
            throw new ValidationException(sprintf('No permissions to save customer to folder "%s"',
                $this->getTemporaryCustomerFolder()->getParent()));
        }

        $customer = $customerProvider->createCustomerInstance();
        $customer->setParent($this->getTemporaryCustomerFolder());
        $customer->setKey('New Customer');
        $customer->setActive(true);
        $customer->setEmail('dummy@customer.com');
        Objects::checkObjectKey($customer);
        $customer->save();

        // return id of new object
        return $this->jsonResponse([
            'success' => true,
            'id' => $customer->getId(),
        ]);
    }

    /**
     * Fetch customer folder object
     *
     * @throws \Exception
     */
    protected function getTemporaryCustomerFolder(): \Pimcore\Model\Asset\Folder | Folder | \Pimcore\Model\Document\Folder
    {
        // fetch customer temp directory
        $tempDirectory = $this->getParameter('pimcore_customer_management_framework.customer_provider.newCustomersTempDir');

        return Service::createFolderByPath($tempDirectory);
    }

    /**
     * Check if current user has access to temporary customer folder
     *
     * @throws \Exception
     */
    protected function hasUserAccessToTempCustomerFolder(): bool
    {
        $folder = $this->getTemporaryCustomerFolder();

        return $folder->isAllowed('save');
    }

    /**
     * @internal
     */
    protected function getExporter(Listing\Concrete $listing, string $exporterName): ExporterInterface
    {
        if (!$this->exporterManager->hasExporter($exporterName)) {
            throw new \InvalidArgumentException('Exporter does not exist');
        }

        return $this->exporterManager->buildExporter($exporterName, $listing);
    }

    /**
     * Load all segment groups
     *
     * @return CustomerSegmentGroup[]
     */
    public function loadSegmentGroups(): array
    {
        if (is_null($this->segmentGroups)) {
            $segmentGroups = $this->getSearchHelper()->getSegmentManager()->getSegmentGroups();
            $segmentGroups->addConditionParam('showAsFilter = 1');
            // sort by filterSortOrder high to low
            $segmentGroups->setOrderKey('filterSortOrder IS NULL, filterSortOrder DESC', false);
            $this->segmentGroups = $segmentGroups->load();
        }

        return $this->segmentGroups;
    }

    protected function buildListing(array $filters = [], array $orders = []): Listing\Concrete
    {
        $listing = $this->getSearchHelper()->getCustomerProvider()->getList();
        $idField = Service::getVersionDependentDatabaseColumnName('id');

        if (array_key_exists('operator-segments', $filters)) {
            if ($filters['operator-segments'] == 'ANY') {
                $listing->setGroupBy($idField, true);
            }
        }

        if (count($orders) > 0) {
            $listing
                ->setOrderKey(array_keys($orders), false)
                ->setOrder(array_values($orders));
        } else {
            $listing
                ->setOrderKey($idField)
                ->setOrder('ASC');
        }

        $this->getSearchHelper()->addListingFilters($listing, $filters, $this->getPimcoreUser());

        return $listing;
    }

    /**
     * Fetch filters and set them on view
     */
    protected function fetchListFilters(Request $request): array
    {
        $filters = $request->get('filter', []);
        $filters = $this->addPrefilteredSegmentToFilters($request, $filters);
        $filters = $this->addFilterDefinitionCustomer($request, $filters);

        return $filters;
    }

    /**
     * Fetch orders and set them on view
     */
    protected function fetchListOrder(Request $request): array
    {
        $orders = $request->get('order', []);
        $ordersNullsLast = [];

        foreach ($orders as $key => $val) {
            if (strtolower($val) == 'asc') {
                $ordersNullsLast['ISNULL(`'.$key.'`)'] = strtoupper($val);
                $ordersNullsLast['(`'.$key.'` = "")'] = strtoupper($val);
            }
            $ordersNullsLast['TRIM(`'.$key.'`)'] = strtoupper($val);
        }

        return $ordersNullsLast;
    }

    protected function addPrefilteredSegmentToFilters(Request $request, array $filters): array
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

        return $this->addFilterDefinitionSegments($request, $filters);
    }

    protected function fetchPrefilteredSegment(Request $request): ?CustomerSegmentInterface
    {
        $segmentId = $request->get('segmentId');

        if ($segmentId) {
            $segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentById($segmentId);
            if (!$segment) {
                throw new \InvalidArgumentException(sprintf('Segment %d was not found', $segmentId));
            }

            return $segment;
        }

        return null;
    }

    /**
     * Fetch all filter definitions available for current user
     *
     * @return FilterDefinition[]
     */
    protected function getFilterDefinitions(): array
    {
        // load filter definitions
        $FilterDefinitionListing = new FilterDefinition\Listing();
        // build user ids condition for filter definition
        $FilterDefinitionListing->setUserIdsCondition($this->getUserIds());
        // return loaded filter definitions array
        return $FilterDefinitionListing->load();
    }

    /**
     * Fetch the FilterDefinition object selected in request
     *
     * @return null|FilterDefinition Returns FilterDefinition object if definition key is defined in filters array,
     * FilterDefinition with id in DB and user is allowed to use FilterDefinition. Otherwise returns null.
     */
    protected function getFilterDefinition(Request $request): ?FilterDefinition
    {
        // fetch filter definition information
        $filterDefinitionData = $request->get('filterDefinition', []);
        // build default FilterDefinition object if no selected
        $segmentGroups = $this->loadSegmentGroups();
        $DefaultFilterDefinition = (new FilterDefinition())->setShowSegments(Objects::getIdsFromArray($segmentGroups));
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
        if (!$filterDefinition->isUserAllowed($this->getPimcoreUser())) {
            // user is not allowed to use FilterDefinition
            return $DefaultFilterDefinition;
        }
        // return FilterDefinition definition
        return $filterDefinition;
    }

    /**
     * Fetch all user ids of current user and their roles
     */
    protected function getUserIds(): array
    {
        // fetch roles of user
        $userIds = $this->getPimcoreUser()->getRoles();
        // fetch id of user
        $userIds[] = $this->getPimcoreUser()->getId();

        // return user ids
        return $userIds;
    }

    /**
     * Merge FilterDefinition for customer fields with filters array
     */
    protected function addFilterDefinitionCustomer(Request $request, array $filters): array
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
        } else {
            // filter of user more important than filter definition
            $filters = array_merge($filterDefinitionCustomer, $filters);
        }
        // return merged filters array
        return $filters;
    }

    /**
     * Add segment filters from FilterDefinition to filters array
     */
    protected function addFilterDefinitionSegments(Request $request, array $filters): array
    {
        $filters['showSegments'] ??= [];

        // merge filters with filters of filter definition
        $filterDefinition = $this->getFilterDefinition($request);

        // check if filter definitions found
        if (is_null($filterDefinition)) {
            return $filters;
        }

        // fetch definitions for segments / only segments array
        $filterDefinition->cleanUp(false);

        $filterDefinitionSegments = $filterDefinition->getDefinition()['segments'] ?? [];

        if ($filterDefinition->isReadOnly()) {
            // overwrite filters with FilterDefinition definition
            $filters['segments'] = $filterDefinitionSegments;
        } else {
            // filter of user more important than filter definition
            $filters['segments'] = array_replace_recursive($filterDefinitionSegments, $filters['segments'] ?? []);
        }

        // set to filter which segments to show
        $filters['showSegments'] = $request->get('apply-segment-selection') ? $filters['showSegments'] : $filterDefinition->getShowSegments();

        // return merged filters array
        return $filters;
    }

    protected function getSearchHelper(): SearchHelper
    {
        return \Pimcore::getContainer()->get(SearchHelper::class);
    }
}
