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

namespace CustomerManagementFrameworkBundle\RESTApi;

use CustomerManagementFrameworkBundle\Filter\ExportActivitiesFilterParams;
use CustomerManagementFrameworkBundle\Model\Activity\GenericActivity;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\PersistentActivityInterface;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ResourceNotFoundException;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResourceUrlGenerator;
use CustomerManagementFrameworkBundle\RESTApi\Traits\ResponseGenerator;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Symfony\Component\HttpFoundation\Request;

class ActivitiesHandler extends AbstractHandler implements CrudHandlerInterface
{
    use LoggerAware;
    use ResponseGenerator;
    use ResourceUrlGenerator;

    /**
     * GET /activities
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listRecords(Request $request)
    {
        $param = ExportActivitiesFilterParams::fromRequest($request);

        $timestamp = time();

        $pageSize = intval($request->get('pageSize', 100));
        $page = intval($request->get('page', 1));

        $paginator = \Pimcore::getContainer()->get('cmf.activity_store')->getActivitiesDataForWebservice(
            $pageSize,
            $page,
            $param
        );

        $result = [
            'page' => $page,
            'totalPages' => $paginator->getPaginationData()['pageCount'],
            'timestamp' => $timestamp,
            'data' => [],
        ];

        foreach ($paginator as $entry) {

            /**
             * @var ActivityStoreEntryInterface $entry ;
             */
            $result['data'][] = $this->hydrateActivityStoreEntry($entry);
        }

        return new Response($result);
    }

    /**
     * GET /activities/{id}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function readRecord(Request $request)
    {
        $entry = $this->loadActivityStoreEntry($request->get('id'));

        return $this->createActivityEntryResponse($entry);
    }

    /**
     * POST /activities
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createRecord(Request $request)
    {
        $data = $this->getRequestData($request);

        $implementationClass = !empty($data['implementationClass']) ? $data['implementationClass'] : GenericActivity::class;

        if (!(is_subclass_of($implementationClass, ActivityInterface::class))) {
            return $this->createErrorResponse(
                sprintf('%s is not a valid activity implementation class', $implementationClass)
            );
        }

        if (!(is_subclass_of($implementationClass, ActivityInterface::class))) {
            return $this->createErrorResponse(
                sprintf('%s is not a valid activity implementation class', $implementationClass)
            );
        }

        if (!isset($data['customerId'])) {
            return $this->createErrorResponse('customerId required');
        }

        try {
            /** @var ActivityInterface|false $activity */
            $activity = $implementationClass::cmfCreate($data);

            if ($activity && $activity->cmfWebserviceUpdateAllowed()) {
                if (!$activity->getCustomer()) {
                    if (!$customer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById(
                        $data['customerId']
                    )
                    ) {
                        return $this->createErrorResponse(sprintf('customer %s not found', $data['customerId']));
                    }

                    $activity->setCustomer($customer);
                }

                if ($activity instanceof PersistentActivityInterface) {
                    $activity->save();
                }

                $entry = \Pimcore::getContainer()->get('cmf.activity_store')->insertActivityIntoStore($activity);
                $entry = \Pimcore::getContainer()->get('cmf.activity_store')->getEntryById($entry->getId());
            } else {
                return $this->createErrorResponse(
                    sprintf(
                        'creation of activities with implementation class %s not allowed via REST webservice',
                        $implementationClass
                    )
                );
            }
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        $response = $this->createActivityEntryResponse($entry);
        $response->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

    /**
     * PUT /activities/{id}
     *
     * TODO support partial updates as we do now or demand whole object in PUT? Use PATCH for partial requests?
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updateRecord(Request $request)
    {
        $entry = $this->loadActivityStoreEntry($request->get('id'));
        $data = $this->getRequestData($request);

        if (isset($data['implementationClass']) && $data['implementationClass'] != $entry->getImplementationClass()) {
            return $this->createErrorResponse('changing of the implementationClass not allowed via REST webservice');
        }

        try {
            $activity = $entry->getRelatedItem();

            if ($activity && $activity->cmfWebserviceUpdateAllowed()) {
                $activity->cmfUpdateData($data);
                if ($activity instanceof PersistentActivityInterface) {
                    $activity->save();
                }

                \Pimcore::getContainer()->get('cmf.activity_store')->updateActivityInStore($activity, $entry);
                $entry = $this->loadActivityStoreEntry($request->get('id'));
            } else {
                return $this->createErrorResponse(
                    sprintf(
                        'update of activities with implementation class %s not allowed via REST webservice',
                        $entry->getImplementationClass()
                    )
                );
            }
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }

        return $this->createActivityEntryResponse($entry);
    }

    /**
     * DELETE /{id}
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deleteRecord(Request $request)
    {
        $entry = $this->loadActivityStoreEntry($request->get('id'));

        try {
            $activity = $entry->getRelatedItem();

            if ($activity && $activity->cmfWebserviceUpdateAllowed()) {
                if ($activity instanceof PersistentActivityInterface) {
                    $activity->delete();
                }

                \Pimcore::getContainer()->get('cmf.activity_store')->deleteEntry($entry);
            } else {
                return $this->createErrorResponse(
                    sprintf(
                        'deletion of activities with implementation class %s not allowed via REST webservice',
                        $entry->getImplementationClass()
                    )
                );
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
     *
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

        $entry = \Pimcore::getContainer()->get('cmf.activity_store')->getEntryById($id);
        if (!$entry) {
            throw new ResourceNotFoundException(sprintf('Activity with ID %d was not found', $id));
        }

        return $entry;
    }

    /**
     * Create customer response with hydrated customer data
     *
     * @param ActivityStoreEntryInterface $activityStoreEntry
     *
     * @return Response
     */
    protected function createActivityEntryResponse(ActivityStoreEntryInterface $activityStoreEntry)
    {
        $response = $this->createResponse(
            $this->hydrateActivityStoreEntry($activityStoreEntry)
        );

        return $response;
    }

    /**
     * @param ActivityStoreEntryInterface $activityStoreEntry
     *
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

        if ($selfLink = $this->generateResourceApiUrl($activityRow['id'])) {
            $links[] = [
                'rel' => 'self',
                'href' => $selfLink,
                'method' => 'GET',
            ];
        }

        $activityRow['_links'] = $links;

        return $activityRow;
    }
}
