<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Filter\ExportActivitiesFilterParams;
use CustomerManagementFramework\Filter\ExportCustomersFilterParams;
use CustomerManagementFramework\Model\Activity\GenericActivity;
use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\PersistentActivityInterface;
use CustomerManagementFramework\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFramework\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFramework\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFramework\Traits\LoggerAware;

class ActivitiesHandler extends AbstractCrudRoutingHandler
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;

    /**
     * GET /activities
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function listRecords(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $param = ExportActivitiesFilterParams::fromRequest($request);

        $timestamp = time();

        $pageSize = intval($request->getParam('pageSize')) ? : 100;
        $page = intval($request->getParam('page')) ? : 1;

        $paginator = Factory::getInstance()->getActivityStore()->getActivitiesDataForWebservice($pageSize, $page, $param);


        $result = [
            'page' => $page,
            'totalPages' => $paginator->getPages()->pageCount,
            'timestamp' => $timestamp,
            'data' => []
        ];

        foreach($paginator as $entry) {



            /**
             * @var ActivityStoreEntryInterface $entry;
             */
            $result['data'][] = $this->hydrateActivityStoreEntry($entry);
        }

        return new Response($result);
    }

    /**
     * GET /activities/{id}
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function readRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $customer = $this->loadActivityStoreEntry($params);

        return $this->createActivityEntryResponse($customer, $request);
    }

    /**
     * POST /activities
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function createRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $data = $this->getRequestData($request);

        $implementationClass = !empty($data['implementationClass']) ? $data['implementationClass'] : GenericActivity::class;

        if(!(is_subclass_of($implementationClass, ActivityInterface::class))) {
            return $this->createErrorResponse(sprintf('%s is not a valid activity implementation class', $implementationClass));
        }

        if(!(is_subclass_of($implementationClass, ActivityInterface::class))) {
            return $this->createErrorResponse(sprintf('%s is not a valid activity implementation class', $implementationClass));
        }

        if(!isset($data['customerId'])) {
            return $this->createErrorResponse('customerId required');
        }

        try {
            /**
             * @var ActivityInterface $activity
             */
            $activity = \Pimcore::getDiContainer()->call([$implementationClass , 'cmfCreate'], [$data]);


            if($activity && $activity->cmfWebserviceUpdateAllowed()) {

                if(!$activity->getCustomer()) {
                    if(!$customer = Factory::getInstance()->getCustomerProvider()->getById($data['customerId'])) {
                        return $this->createErrorResponse(sprintf("customer %s not found", $data['customerId']));
                    }

                    $activity->setCustomer($customer);
                }

                if($activity instanceof PersistentActivityInterface) {
                    $activity->save();
                }

                $entry = Factory::getInstance()->getActivityStore()->insertActivityIntoStore($activity);

            } else {
                return $this->createErrorResponse(sprintf("creation of activities with implementation class %s not allowed via REST webservice", $implementationClass));
            }
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        $response = $this->createActivityEntryResponse($entry, $request);
        $response->setResponseCode(Response::RESPONSE_CODE_CREATED);

        return $response;
    }

    /**
     * PUT /activities/{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function updateRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $entry = $this->loadActivityStoreEntry($params);
        $data     = $this->getRequestData($request);

        if(isset($data['implementationClass']) && $data['implementationClass'] != $entry->getImplementationClass()) {
            return $this->createErrorResponse("changing of the implementationClass not allowed via REST webservice");
        }

        try {

            $activity = $entry->getRelatedItem();

            if($activity && $activity->cmfWebserviceUpdateAllowed()) {
                $activity->cmfUpdateData($data['attributes']);
                if($activity instanceof PersistentActivityInterface) {
                    $activity->save();
                }

                Factory::getInstance()->getActivityStore()->updateActivityInStore($activity, $entry);
                $entry = $this->loadActivityStoreEntry($params);
            } else {
                return $this->createErrorResponse(sprintf("update of activities with implementation class %s not allowed via REST webservice", $entry->getImplementationClass()));
            }

        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createActivityEntryResponse($entry, $request);
    }

    /**
     * DELETE /activities/{id}
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @return Response
     */
    public function deleteRecord(\Zend_Controller_Request_Http $request, array $params = [])
    {
        $entry = $this->loadActivityStoreEntry($params);

        try {
            $activity = $entry->getRelatedItem();

            if($activity && $activity->cmfWebserviceUpdateAllowed()) {

                if($activity instanceof PersistentActivityInterface) {
                    $activity->delete();
                }

                Factory::getInstance()->getActivityStore()->deleteEntry($entry);

            } else {
                return $this->createErrorResponse(sprintf("deletion of activities with implementation class %s not allowed via REST webservice", $entry->getImplementationClass()));
            }
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createResponse(null, Response::RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * Load a customer from ID/params array. If an array is passed, it tries to resolve the id from the 'id' property
     *
     * @param int|array $id
     * @return ActivityStoreEntryInterface
     */
    protected function loadActivityStoreEntry($id)
    {
        if (is_array($id)) {
            if (!isset($id['id'])) {
                // this should never happen as the route demands an ID in the request
                throw new ResourceNotFoundException('Record ID is missing');
            }

            $id = $id['id'];
        }

        if ($id) {
            $id = (int)$id;
        }

        $entry = Factory::getInstance()->getActivityStore()->getEntryById($id);
        if (!$entry) {
            throw new ResourceNotFoundException(sprintf('Activity with ID %d was not found', $id));
        }

        return $entry;
    }

    /**
     * @param \Zend_Paginator $paginator
     * @param \Zend_Controller_Request_Http $request
     * @param int $defaultPageSize
     * @param int $defaultPage
     */
    protected function handlePaginatorParams(\Zend_Paginator $paginator, \Zend_Controller_Request_Http $request, $defaultPageSize = 100, $defaultPage = 1)
    {
        $pageSize = intval($request->getParam('pageSize', $defaultPageSize));
        $page     = intval($request->getParam('page', $defaultPage));

        $paginator->setItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($page);
    }

    /**
     * Create customer response with hydrated customer data
     *
     * @param ActivityStoreEntryInterface $activityStoreEntry
     * @param \Zend_Controller_Request_Http $request
     * @param ExportCustomersFilterParams $params
     * @return Response
     */
    protected function createActivityEntryResponse(ActivityStoreEntryInterface $activityStoreEntry, \Zend_Controller_Request_Http $request, ExportCustomersFilterParams $params = null)
    {
        if (null === $params) {
            $params = ExportCustomersFilterParams::fromRequest($request);
        }

        $response = $this->createResponse(
            $this->hydrateActivityStoreEntry($activityStoreEntry)
        );

        return $response;
    }

    /**
     * @param ActivityStoreEntryInterface $activityStoreEntry
     * @param ExportCustomersFilterParams $params
     * @return array
     */
    protected function hydrateActivityStoreEntry(ActivityStoreEntryInterface $activityStoreEntry)
    {
        $data = $activityStoreEntry->getData();

        $data = $this->addSelfLink($data);

        return $data;
    }

    protected function addSelfLink(array $activityRow)
    {
        $links = isset($activityRow['_links']) ? $activityRow['_links'] : [];

        if ($selfLink = $this->generateApiUrl($activityRow['id'])) {
            $links[] = [
                'rel'    => 'self',
                'href'   => $selfLink,
                'method' => 'GET'
            ];
        }

        $activityRow['_links'] = $links;

        return $activityRow;
    }

    /**
     * Generate record URL
     *
     * @param int $id
     * @return string|null
     */
    protected function generateApiUrl($id)
    {
        if (!$this->urlHelper || !$this->apiResourceRoute) {
            return null;
        }

        return $this->urlHelper->url([
            'id' => $id
        ], $this->apiResourceRoute, true);
    }
}
