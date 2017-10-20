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
use CustomerManagementFrameworkBundle\CustomerList\Exporter\AbstractExporter;
use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use CustomerManagementFrameworkBundle\CustomerList\ExporterManagerInterface;
use CustomerManagementFrameworkBundle\CustomerList\Filter\CustomerSegment as CustomerSegmentFilter;
use CustomerManagementFrameworkBundle\CustomerList\Filter\Exception\SearchQueryException;
use CustomerManagementFrameworkBundle\CustomerList\Filter\SearchQuery;
use CustomerManagementFrameworkBundle\Listing\Filter;
use CustomerManagementFrameworkBundle\Listing\FilterHandler;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Db;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Paginator\Adapter\ArrayAdapter;

/**
 * @Route("/customers")
 */
class CustomersController extends Admin
{
    // params still needed when clearing all filters
    protected $clearUrlParams = [];

    public function onKernelController(FilterControllerEvent $event)
    {
        parent::onKernelController($event);
        $this->checkPermission('plugin_cmf_perm_customerview');

        \Pimcore\Model\DataObject\AbstractObject::setHideUnpublished(true);
    }

    /**
     * @param Request $request
     * @Route("/list")
     */
    public function listAction(Request $request)
    {
        $filters = $this->fetchListFilters($request);

        $errors = [];
        $paginator = null;
        $customerView = \Pimcore::getContainer()->get('cmf.customer_view');

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
            ]
        );
    }

    /**
     * @param Request $request
     * @Route("/detail")
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
            'exporter' => $request->get('exporter')
        ]);

        return $this->json([
            'url' => $this->generateUrl('customermanagementframework_admin_customers_exportstep', ['jobId' => $jobId]),
            'jobId' => $jobId,
            'exporter' => $request->get('exporter')
        ]);
    }

    /**
     * @param Request $request
     * @route("/export-step")
     */
    public function exportStepAction(Request $request)
    {
        $perRequest = $request->get('perRequest', \Pimcore::getContainer()->getParameter('cmf.customer_export.items_per_request'));

        try {
            $data = \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->getExportTmpData($request);
        } catch (\Exception $e) {
            return $this->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }

        //export finished
        if (!sizeof($data['processIds'])) {
            return $this->json([
                'finished' => true,
                'url' => $this->generateUrl('customermanagementframework_admin_customers_downloadfinishedexport', ['jobId' => $request->get('jobId')]),
                'jobId' => $request->get('jobId')
            ]);
        }

        $ids = array_slice($data['processIds'], 0, $perRequest);
        $processIds = array_slice($data['processIds'], $perRequest);

        $listing = $this->buildListing();
        $listing->addConditionParam('o_id in (' . implode(', ', $ids) . ')');

        $exporter = $this->getExporter($listing, $data['exporter']);
        $exportData = $exporter->getExportData();

        $totalExportData = isset($data['exportData']) ? $data['exportData'] : [];
        $totalExportData = array_merge_recursive($totalExportData, $exportData);

        $data['exportData'] = $totalExportData;
        $data['processIds'] = $processIds;

        \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->saveExportTmpData($request->get('jobId'), $data);

        $notProcessedRecordsCount = sizeof($data['processIds']);
        $totalRecordsCount = $notProcessedRecordsCount + sizeof($data['exportData'][AbstractExporter::ROWS]);

        $percent = round(($totalRecordsCount - $notProcessedRecordsCount) * 100 / $totalRecordsCount, 0);

        return $this->json([
            'finished' => false,
            'jobId' => $request->get('jobId'),
            'notProcessedRecordsCount' => $notProcessedRecordsCount,
            'totalRecordsCount' => $totalRecordsCount,
            'percent' => $percent,
            'progress' => sprintf('%s/%s (%s %%)', ($totalRecordsCount - $notProcessedRecordsCount), $totalRecordsCount, $percent)

        ]);
    }

    /**
     * @param Request $request
     * @route("/download-finished-export")
     */
    public function downloadFinishedExportAction(Request $request)
    {
        try {
            $data = \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->getExportTmpData($request);
        } catch (\Exception $e) {
            return $this->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }

        if (sizeof($data['processIds'])) {
            return $this->json([
                'error' => true,
                'message' => 'export not finished yet'
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
     * @param Request $request
     *
     * @return ExporterInterface
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
        $segmentGroups = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroups();
        $segmentGroups->addConditionParam('showAsFilter = 1');

        return $segmentGroups->load();
    }

    /**
     * @param array $filters
     *
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
}
