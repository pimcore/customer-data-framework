<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\Traits\ApplicationLoggerAware;
use DrewM\MailChimp\MailChimp;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;

class SegmentExporter
{
    use ApplicationLoggerAware;
    
    /**
     * @var MailChimpExportService
     */
    private $exportService;

    private $lastCreatedGroupRemoteId;

    /**
     * SegmentExporter constructor.
     * @param MailChimp $apiClient
     * @param string $listId
     */
    public function __construct(MailChimpExportService $exportService)
    {
        $this->exportService = $exportService;

        $this->setLoggerComponent('NewsletterSync');
    }

    /**
     * Export a segment group
     *
     * @param CustomerSegmentGroup $group
     * @param bool $forceCreate
     *
     * @return null|string
     */
    public function exportGroup(CustomerSegmentGroup $group, $listId, $forceCreate = false, $forceUpdate = false)
    {
        $exportService = $this->exportService;
        $apiClient = $exportService->getApiClient();

        $data = [
            'title' => $group->getName(),
            'type' => 'checkboxes',
        ];

        $remoteGroupId = null;
        $result = null;
        $isEdit = false;

        if ($forceCreate) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][GROUP %s] Forcing creation of group %s',
                    $group->getId(),
                    $group->getName()
                ),
                [
                    'relatedObject' => $group
                ]
            );
        }

        if ($forceCreate || !$exportService->wasExported($group, $listId)) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][GROUP %s] Creating group %s',
                    $group->getId(),
                    $group->getName(),
                    $remoteGroupId
                ),
                [
                    'relatedObject' => $group
                ]
            );

            $result = $apiClient->post(
                $exportService->getListResourceUrl($listId, 'interest-categories'),
                $data
            );

            if ($apiClient->success()) {
                $remoteGroupId = $result['id'];
            }

            $this->lastCreatedGroupRemoteId = $remoteGroupId;
        } else {
            $isEdit = true;
            $remoteGroupId = $exportService->getRemoteId($group, $listId);

            if(!$forceUpdate && !$exportService->needsUpdate($group, $listId)) {
                $this->getLogger()->debug(
                    sprintf(
                        '[MailChimp][GROUP %s] Updating group %s with remote ID %s skipped - no update needed',
                        $group->getId(),
                        $group->getName(),
                        $remoteGroupId
                    ),
                    [
                        'relatedObject' => $group
                    ]
                );
                return $remoteGroupId;
            }


            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][GROUP %s] Updating group %s with remote ID %s',
                    $group->getId(),
                    $group->getName(),
                    $remoteGroupId
                ),
                [
                    'relatedObject' => $group
                ]
            );

            $result = $apiClient->patch(
                $exportService->getListResourceUrl(
                    $listId,
                    sprintf('interest-categories/%s', $remoteGroupId)
                ),
                $data
            );
        }

        if ($apiClient->success()) {
            $this->getLogger()->notice(
                sprintf(
                    '[MailChimp][GROUP %s] Request was successful for group %s. Remote ID is %s',
                    $group->getId(),
                    $group->getName(),
                    $remoteGroupId
                ),
                [
                    'relatedObject' => $group
                ]
            );

            // add note
            $exportService
                ->createExportNote($group, $listId, $remoteGroupId)
                ->save();
        } else {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][GROUP %s] Failed to export group %s: %s %s',
                    $group->getId(),
                    $group->getName(),
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body']
                ),
                [
                    'relatedObject' => $group
                ]
            );

            // we tried to edit a resource which doesn't exist (anymore) - fall back to create
            if ($isEdit && isset($result['status']) && $result['status'] === 404) {
                $this->getLogger()->warning(
                    sprintf(
                        '[MailChimp][GROUP %s] Edit request was a 404 - falling back to create group %s',
                        $group->getId(),
                        $group->getName()
                    ),
                    [
                        'relatedObject' => $group
                    ]
                );

                return $this->exportGroup($group, $listId, true);
            }

            return null;
        }

        return $remoteGroupId;
    }

    /**
     * Export a segment
     *
     * @param CustomerSegment $segment
     * @param string $listId
     * @param string $remoteGroupId
     * @param bool $forceCreate
     * @param bool $forceUpdate
     *
     * @return null|string
     */
    public function exportSegment(CustomerSegment $segment, $listId, $remoteGroupId, $forceCreate = false, $forceUpdate = false)
    {
        $exportService = $this->exportService;
        $apiClient = $exportService->getApiClient();
        $data = [
            'name' => $segment->getName(),
        ];
        $remoteSegmentId = null;
        $result = null;
        $isEdit = false;
        if ($forceCreate) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][SEGMENT %s] Forcing creation of segment %s',
                    $segment->getId(),
                    $segment->getName()
                ),
                [
                    'relatedObject' => $segment
                ]
            );
        }
        if ($forceCreate || !$exportService->wasExported($segment, $listId)) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][SEGMENT %s] Creating segment %s',
                    $segment->getId(),
                    $segment->getName(),
                    $remoteSegmentId
                ),
                [
                    'relatedObject' => $segment
                ]
            );
            $result = $apiClient->post(
                $exportService->getListResourceUrl(
                    $listId,
                    sprintf('interest-categories/%s/interests', $remoteGroupId)
                ),
                $data
            );
            if ($apiClient->success()) {
                $remoteSegmentId = $result['id'];
            }
        } else {
            $isEdit = true;
            $remoteSegmentId = $exportService->getRemoteId($segment, $listId);

            if(!$forceUpdate && !$exportService->needsUpdate($segment, $listId)) {
                $this->getLogger()->debug(
                    sprintf(
                        '[MailChimp][SEGMENT %s] Updating segment %s with remote ID %s skipped - no update needed',
                        $segment->getId(),
                        $segment->getName(),
                        $remoteSegmentId
                    ),
                    [
                        'relatedObject' => $segment
                    ]
                );

                return $remoteSegmentId;
            }

            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][SEGMENT %s] Updating segment %s with remote ID %s',
                    $segment->getId(),
                    $segment->getName(),
                    $remoteSegmentId
                ),
                [
                    'relatedObject' => $segment
                ]
            );
            $result = $apiClient->patch(
                $exportService->getListResourceUrl(
                    $listId,
                    sprintf('interest-categories/%s/interests/%s', $remoteGroupId, $remoteSegmentId)
                ),
                $data
            );
        }
        if ($apiClient->success()) {
            $this->getLogger()->notice(
                sprintf(
                    '[MailChimp][SEGMENT %s] Request was successful for segment %s. Remote ID is %s',
                    $segment->getId(),
                    $segment->getName(),
                    $remoteGroupId
                ),
                [
                    'relatedObject' => $segment
                ]
            );
            // add note
            $exportService
                ->createExportNote($segment, $listId, $remoteSegmentId)
                ->save();
        } else {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][SEGMENT %s] Failed to export segment %s: %s %s',
                    $segment->getId(),
                    $segment->getName(),
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body']
                ),
                [
                    'relatedObject' => $segment
                ]
            );
            // we tried to edit a resource which doesn't exist (anymore) - fall back to create
            if ($isEdit && isset($result['status']) && $result['status'] === 404) {
                $this->getLogger()->error(
                    sprintf(
                        '[MailChimp][SEGMENT %s] Edit request was a 404 - falling back to create %s',
                        $segment->getId(),
                        $segment->getName()
                    ),
                    [
                        'relatedObject' => $segment
                    ]
                );
                return $this->exportSegment($segment, $listId, $remoteGroupId, true);
            }
            return null;
        }
        return $remoteSegmentId;
    }

    /**
     * deletes all segments from given $remoteGroupId in mailchimp which are not within the given $existingSegmentIds array
     *
     * @param array $existingGroupIds
     * @param string $listId
     * @param string $remoteGroupId
     */
    public function deleteNonExistingSegmentsFromGroup(array $existingSegmentIds, $listId, $remoteGroupId)
    {
        $exportService = $this->exportService;
        $apiClient = $exportService->getApiClient();

        $result = $apiClient->get(
            $exportService->getListResourceUrl($listId, 'interest-categories/' . $remoteGroupId . '/interests')
        );

        if(isset($result['interests'])) {
            foreach($result['interests'] as $interest) {
                if(in_array($interest['id'], $existingSegmentIds)) {
                    continue;
                }

                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][Segment] Deleting segments with remote ID %s within group ID %s',
                        $interest['id'],
                        $remoteGroupId
                    )
                );

                $apiClient->delete(
                    $exportService->getListResourceUrl($listId,'interest-categories/' . $remoteGroupId . '/interests/' . $interest['id'])
                );

                if(!$apiClient->success()) {
                    $this->getLogger()->error(
                        sprintf(
                            '[MailChimp][Segment] Deleting segments with remote ID %s within group ID %s failed: %s %s',
                            $interest['id'],
                            $remoteGroupId,
                            json_encode($apiClient->getLastError()),
                            $apiClient->getLastResponse()['body']
                        )
                    );
                }
            }
        }
    }

    /**
     * deletes all groups in mailchimp which are not within the given $existingGroupIds array
     *
     * @param array $existingGroupIds
     * @param string $listId
     */
    public function deleteNonExistingGroups(array $existingGroupIds, $listId)
    {
        $exportService = $this->exportService;
        $apiClient = $exportService->getApiClient();

        $url = $exportService->getListResourceUrl($listId,'interest-categories');

        $result = $apiClient->get(
            $url
        );

        foreach($result['categories'] as $category) {
            if(!in_array($category['id'], $existingGroupIds)) {
                $this->deleteGroupByRemoteId($category['id'], $listId);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getLastCreatedGroupRemoteId()
    {
        return $this->lastCreatedGroupRemoteId;
    }



    /**
     * @param string $remoteGroupId
     * @param string $listId
     */
    private function deleteGroupByRemoteId( $remoteGroupId, $listId )
    {
        $group = $this->exportService->getObjectByRemoteId( $remoteGroupId );

        if($group instanceof CustomerSegmentGroup) {

            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][GROUP %s] Deleting group %s with remote ID %s',
                    $group->getId(),
                    $group->getName(),
                    $remoteGroupId
                ),
                [
                    'relatedObject' => $group
                ]
            );
        } else {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][GROUP] Deleting group with remote ID %s',
                    $remoteGroupId
                )
            );
        }

        $exportService = $this->exportService;
        $apiClient = $exportService->getApiClient();

        $apiClient->delete(
            $exportService->getListResourceUrl($listId,'interest-categories/' . $remoteGroupId)
        );

        if(!$apiClient->success()) {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][GROUP] Failed to delete group with remote ID %s: %s %s',
                    $remoteGroupId,
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body']
                ),
                [
                    'relatedObject' => $group
                ]
            );
        }

    }
}