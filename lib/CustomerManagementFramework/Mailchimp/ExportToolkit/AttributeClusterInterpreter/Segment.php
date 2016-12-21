<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter;

use CustomerManagementFramework\Factory;
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

            if (!$group) {
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
                dumpCli('GROUP FAILED - ABORT');
                continue;
            }

            foreach ($groupData['segments'] as $segment) {
                $this->exportSegment($segment, $remoteGroupId, $groupIsNew);
            }
        }
    }

    protected function exportGroup(CustomerSegmentGroup $group)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        $data = [
            'title' => $group->getName(),
            'type'  => 'checkboxes'
        ];

        dumpCli($data);

        $remoteGroupId = null;
        $result        = null;

        $debugPrefix = 'CREATE';

        if ($exportService->wasExported($group)) {
            $debugPrefix = 'UPDATE';

            $remoteGroupId = $exportService->getRemoteId($group);

            $result = $apiClient->patch(
                $exportService->getListResourceUrl(
                    sprintf('interest-categories/%s', $remoteGroupId)
                ),
                $data
            );
        } else {
            $result = $apiClient->post(
                $exportService->getListResourceUrl('interest-categories'),
                $data
            );

            if ($apiClient->success()) {
                $remoteGroupId = $result['id'];
            }
        }

        dumpCli($apiClient->getLastRequest());
        dumpCli($apiClient->getLastResponse());
        dumpCli($result);

        if ($apiClient->success()) {
            dumpCli($debugPrefix . ' GROUP SUCCESS');

            $exportService
                ->createExportNote($group, $remoteGroupId)
                ->save();
        } else {
            dumpCli($debugPrefix . ' GROUP ERROR', $apiClient->getLastError());

            return null;
        }

        return $remoteGroupId;
    }

    protected function exportSegment(CustomerSegment $segment, $remoteGroupId, $forceCreate = false)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        $data = [
            'name' => $this->data[$segment->getId()]['name']
        ];

        $remoteSegmentId = null;
        $result          = null;

        $debugPrefix = 'CREATE';

        if ($exportService->wasExported($segment)) {
            $debugPrefix = 'UPDATE';

            $remoteSegmentId = $exportService->getRemoteId($segment);

            $result = $apiClient->patch(
                $exportService->getListResourceUrl(
                    sprintf('interest-categories/%s/interests/%s', $remoteGroupId, $remoteSegmentId)
                ),
                $data
            );
        } else {
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

        dumpCli($apiClient->getLastRequest());
        dumpCli($apiClient->getLastResponse());
        dumpCli($result);

        if ($apiClient->success()) {
            dumpCli($debugPrefix . ' SEGMENT SUCCESS');

            $exportService
                ->createExportNote($segment, $remoteSegmentId)
                ->save();
        } else {
            dumpCli($debugPrefix . ' SEGMENT ERROR', $apiClient->getLastError());

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
