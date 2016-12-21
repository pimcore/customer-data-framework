<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;

class Segment extends AbstractMailchimpInterpreter
{
    /**
     * This method is executed before the export is launched.
     * For example it can be used to clean up old export files, start a database transaction, etc.
     * If not needed, just leave the method empty.
     *
     */
    public function setUpExport()
    {
        // TODO: Implement setUpExport() method.
    }

    /**
     * This method is executed after all defined attributes of an object are exported.
     * The to-export data is stored in the array $this->data[OBJECT_ID].
     * For example it can be used to write each exported row to a destination database,
     * write the exported entries to a file, etc.
     * If not needed, just leave the method empty.
     *
     * @param AbstractObject $object
     */
    public function commitDataRow(AbstractObject $object)
    {
        // TODO: Implement commitDataRow() method.
    }

    /**
     * This method is executed after all objects are exported.
     * If not cleaned up in the commitDataRow-method, all exported data is stored in the array $this->data.
     * For example it can be used to write all data to a xml file or commit a database transaction, etc.
     *
     */
    public function commitData()
    {
        $categorized   = $this->categorizeData();
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        foreach ($categorized as $groupData) {
            $remoteGroupId = null;

            /** @var CustomerSegmentGroup $group */
            $group = $groupData['group'];

            // used for log messages
            $segmentIds = array_map(function(CustomerSegmentInterface $segment) {
                return $segment->getId();
            }, $groupData['segments']);

            if (!$group) {
                $this->logger->warning(sprintf(
                    '[MailChimp] Skipping exports of segment(s) %s as they have no group',
                    implode(', ', $segmentIds)
                ));

                // TODO add support for segments without group (default group)
                continue;
            }

            // optimization - do not look if segment was exported if the group is new
            $groupIsNew = true;
            if ($exportService->wasExported($group)) {
                $groupIsNew = false;
            }

            $remoteGroupId = $this->exportGroup($group);
            if (!$remoteGroupId) {
                $this->logger->error(sprintf(
                    '[MailChimp][GROUP %s] Failed to export group %s - skipping export of segment(s) %s',
                    $group->getId(),
                    $group->getName(),
                    implode(', ', $segmentIds)
                ));

                continue;
            }

            foreach ($groupData['segments'] as $segment) {
                $this->exportSegment($segment, $remoteGroupId, $groupIsNew);
            }
        }
    }

    /**
     * Export a segment group
     *
     * @param CustomerSegmentGroup $group
     * @return null|string
     */
    protected function exportGroup(CustomerSegmentGroup $group)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        $data = [
            'title' => $group->getName(),
            'type'  => 'checkboxes'
        ];

        $remoteGroupId = null;
        $result        = null;

        if ($exportService->wasExported($group)) {
            $remoteGroupId = $exportService->getRemoteId($group);

            $this->logger->info(sprintf(
                '[MailChimp][GROUP %s] Updating group %s with remote ID %s',
                $group->getId(),
                $group->getName(),
                $remoteGroupId
            ));

            $result = $apiClient->patch(
                $exportService->getListResourceUrl(
                    sprintf('interest-categories/%s', $remoteGroupId)
                ),
                $data
            );
        } else {
            $this->logger->info(sprintf(
                '[MailChimp][GROUP %s] Creating group %s',
                $group->getId(),
                $group->getName(),
                $remoteGroupId
            ));

            $result = $apiClient->post(
                $exportService->getListResourceUrl('interest-categories'),
                $data
            );

            if ($apiClient->success()) {
                $remoteGroupId = $result['id'];
            }
        }

        if ($apiClient->success()) {
            $this->logger->info(sprintf(
                '[MailChimp][GROUP %s] Request was successful for group %s. Remote ID is %s',
                $group->getId(),
                $group->getName(),
                $remoteGroupId
            ));

            $exportService
                ->createExportNote($group, $remoteGroupId)
                ->save();
        } else {
            $this->logger->error(sprintf(
                '[MailChimp][GROUP %s] Failed to export group %s: %s %s',
                $group->getId(),
                $group->getName(),
                json_encode($apiClient->getLastError()),
                $apiClient->getLastResponse()['body']
            ));

            return null;
        }

        return $remoteGroupId;
    }

    /**
     * Export a segment
     *
     * @param CustomerSegment $segment
     * @param $remoteGroupId
     * @param bool $forceCreate
     * @return null|string
     */
    protected function exportSegment(CustomerSegment $segment, $remoteGroupId, $forceCreate = false)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        $data = [
            'name' => $this->data[$segment->getId()]['name']
        ];

        $remoteSegmentId = null;
        $result          = null;

        if ($exportService->wasExported($segment)) {
            $remoteSegmentId = $exportService->getRemoteId($segment);

            $this->logger->info(sprintf(
                '[MailChimp][SEGMENT %s] Updating segment %s with remote ID %s',
                $segment->getId(),
                $segment->getName(),
                $remoteSegmentId
            ));

            $result = $apiClient->patch(
                $exportService->getListResourceUrl(
                    sprintf('interest-categories/%s/interests/%s', $remoteGroupId, $remoteSegmentId)
                ),
                $data
            );
        } else {
            $this->logger->info(sprintf(
                '[MailChimp][SEGMENT %s] Creating segment %s',
                $segment->getId(),
                $segment->getName(),
                $remoteSegmentId
            ));

            $result = $apiClient->post(
                $exportService->getListResourceUrl(
                    sprintf('interest-categories/%s/interests', $remoteGroupId)
                ),
                $data
            );

            if ($apiClient->success()) {
                $remoteSegmentId = $result['id'];
            }
        }

        if ($apiClient->success()) {
            $this->logger->info(sprintf(
                '[MailChimp][SEGMENT %s] Request was successful for segment %s. Remote ID is %s',
                $segment->getId(),
                $segment->getName(),
                $remoteGroupId
            ));

            $exportService
                ->createExportNote($segment, $remoteSegmentId)
                ->save();
        } else {
            $this->logger->error(sprintf(
                '[MailChimp][SEGMENT %s] Failed to export segment %s: %s %s',
                $segment->getId(),
                $segment->getName(),
                json_encode($apiClient->getLastError()),
                $apiClient->getLastResponse()['body']
            ));

            return null;
        }

        return $remoteSegmentId;
    }

    /**
     * Categorize data by group => segments
     *
     * @return array
     */
    protected function categorizeData()
    {
        $categorized = [];

        foreach (array_keys($this->data) as $segmentId) {
            $segment  = Factory::getInstance()->getSegmentManager()->getSegmentById($segmentId);
            $group    = $segment->getGroup();

            $groupKey = '__default';
            if ($group) {
                $groupKey = $group->getId();
            }

            if (!isset($categorized[$groupKey])) {
                $categorized[$groupKey] = [
                    'groupKey' => $groupKey,
                    'group'    => $group,
                    'segments' => []
                ];
            }

            $categorized[$groupKey]['segments'][] = $segment;
        }

        return $categorized;
    }

    public function getRemoteSegmentGroups()
    {
    }

    /**
     * This method is executed of an object is not exported (anymore).
     * For example it can be used to remove the entries from a destination database, etc.
     *
     * @param AbstractObject $object
     */
    public function deleteFromExport(AbstractObject $object)
    {
        // TODO: Implement deleteFromExport() method.
    }
}
