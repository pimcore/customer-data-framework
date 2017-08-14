<?php

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\CustomerList\ExporterManagerInterface;
use CustomerManagementFrameworkBundle\Listing\Filter;
use CustomerManagementFrameworkBundle\Listing\FilterHandler;
use CustomerManagementFrameworkBundle\Controller\Admin;
use CustomerManagementFrameworkBundle\CustomerList\Filter\Exception\SearchQueryException;
use CustomerManagementFrameworkBundle\CustomerList\Filter\SearchQuery;
use CustomerManagementFrameworkBundle\CustomerList\Filter\CustomerSegment as CustomerSegmentFilter;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Pimcore\Model\Object\Listing;
use Pimcore\Model\Tool\TmpStore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Controller\Configuration\TemplatePhp;
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
        $this->checkPermission('plugin_customermanagementframework_customerview');

        \Pimcore\Model\Object\AbstractObject::setHideUnpublished(true);
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

        $exporterName = $request->get('exporter', 'csv');
        /**
         * @var ExporterManagerInterface $exporterManager
         */
        $exporterManager = \Pimcore::getContainer()->get('cmf.customer_exporter_manager');

        if (!$exporterManager->hasExporter($exporterName)) {
            throw new \InvalidArgumentException('Exporter does not exist');
        }

        $filters = $this->fetchListFilters($request);
        $listing = $this->buildListing($filters);
        $exporter = $exporterManager->buildExporter($exporterName, $listing);

        $filename = sprintf(
            '%s-%s-segment-export.%s',
            $exporterName,
            \Carbon\Carbon::now()->format('YmdHis'),
            $exporter->getExtension()
        );

        $exportData = $exporter->getExportData();

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

        return $response;
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

        $filterProperties = Config::getConfig()->CustomerList->filterProperties;

        $equalsProperties = isset($filterProperties->equals) ? $filterProperties->equals->toArray() : [];
        $searchProperties = isset($filterProperties->search) ? $filterProperties->search->toArray() : [];

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
                    /** @var \Pimcore\Model\Object\CustomerSegmentGroup $segmentGroup */
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
        $filterProperties = Config::getConfig()->CustomerList->filterProperties;
        $searchProperties = isset($filterProperties->search) ? $filterProperties->search->toArray() : [];

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
