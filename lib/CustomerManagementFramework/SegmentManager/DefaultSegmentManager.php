<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:02
 */

namespace CustomerManagementFramework\SegmentManager;


use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use CustomerManagementFramework\SegmentBuilder\SegmentBuilderInterface;
use Pimcore\File;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\Customer;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Pimcore\Model\Object\Service;
use Psr\Log\LoggerInterface;

class DefaultSegmentManager implements SegmentManagerInterface {
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND)
    {
        $list = new \Pimcore\Model\Object\Customer\Listing;
        $list->setUnpublished(false);

        $conditions = [];
        foreach($segmentIds as $segmentId) {
            $conditions[] = "(o_id in (select src_id from object_relations_1 where dest_id = " . $list->quote($segmentId) ."))";
        }

        if(sizeof($conditions)) {
            $list->setCondition("(" . implode(' ' . $conditionMode . ' ', $conditions)  . ")");
        }


        return $list;
    }

    public function getSegmentById($segmentId)
    {
        // TODO: Implement getSegmentById() method.
    }

    public function getSegments(array $params)
    {
        $list = CustomerSegment::getList();
        $list->setUnpublished(false);

        return $list->load();
    }

    /**
     * @param array $segmentBuilderConfigs
     *
     * @return void
     */
    public function buildCalculatedSegments(LoggerInterface $logger)
    {
        $logger->notice("start segment building");

        $config = Plugin::getConfig()->segmentBuilders;

        if(is_null($config)) {
            $logger->alert("no segmentBuilders section found in plugin config file");
            return;
        }

        if(!sizeof($config)) {
            $logger->alert("no segment builders defined in plugin config file");
            return;
        }

        $segmentBuilders = self::createSegmentBuilders($logger, $config);
        self::prepareSegmentBuilders($segmentBuilders, $logger);

        $customerList = new \Pimcore\Model\Object\Customer\Listing;
        $paginator = new \Zend_Paginator($customerList);
        $paginator->setItemCountPerPage(100);

        $totalPages = $paginator->getPages()->pageCount;
        for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
            $logger->notice(sprintf("build customer segments page %s of %s", $pageNumber, $totalPages));
            $paginator->setCurrentPageNumber($pageNumber);

            foreach($paginator as $customer) {
                foreach($segmentBuilders as $segmentBuilder) {
                    $logger->info(sprintf("apply segment builder %s to customer %s", $segmentBuilder->getName(), (string)$customer));

                    $segmentBuilder->calculateSegments($customer, $this);
                }
                exit;
            }
        }
    }

    public function buildCalculatedSegmentsOnCustomerSave(CustomerInterface $customer)
    {

    }

    public function mergeCalculatedSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = [])
    {
        $currentSegments = (array)$customer->getCalculatedSegments();

        $saveNeeded = false;
        foreach ($addSegments as $segment) {
            $found = false;
            foreach ($currentSegments as $seg) {
                if ($segment->getId() == $seg->getId()) {
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                $saveNeeded = true;
                $currentSegments[] = $segment;
            }
        }

        foreach($currentSegments as $key => $segment)
        {
            foreach($deleteSegments as $deleteSegment) {
                if($segment->getId() == $deleteSegment->getId()) {
                    unset($currentSegments[$key]);
                    $saveNeeded = true;
                }
            }
        }


        if($saveNeeded) {
            $backup = \Pimcore\Model\Version::$disabled;
            \Pimcore\Model\Version::disable();
            $customer->setCalculatedSegments($currentSegments);
            $customer->save();

            if(!$backup) {
                \Pimcore\Model\Version::enable();
            }
        }
    }

    /**
     * @TODO
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null)
    {
        $segmentGroup = self::createSegmentGroup($segmentGroup, $segmentGroup, true);

        $list = new \Pimcore\Model\Object\CustomerSegment\Listing;

        $list->setCondition("reference = ?", $segmentReference);
        $list->setUnpublished(true);
        $list->setLimit(1);
        $list = $list->load();

        if($segment = $list[0]) {
            return $segment;
        }

        $segment = new CustomerSegment();

        $segment->setParent($segmentGroup);
        $segment->setKey(\Pimcore\Model\Element\Service::getValidKey($segmentReference, 'object'));
        $segment->setName($segmentName ? : $segmentReference);
        $segment->setReference($segmentReference);
        $segment->setPublished(true);
        $segment->setCalculated(true);
        $segment->setGroup($segmentGroup);
        $segment->save();
        return $segment;
    }

    public function createSegmentGroup($segmentGroupName, $segmentGroupReference = null, $calculated = false)
    {
        if(!is_null($segmentGroupReference)) {

            $list = new \Pimcore\Model\Object\CustomerSegmentGroup\Listing;
            $list->setUnpublished(true);

            $calculatedWhere = "(calculated is null or calculated = 0)";
            if($calculated) {
                $calculatedWhere = "(calculated = 1)";
            }

            $list->setCondition("reference = ? and ".$calculatedWhere, $segmentGroupReference);
            $list->setUnpublished(true);
            $list->setLimit(1);
            $list = $list->load();

            if($list[0]) {
                return $list[0];
            }
        }

        $segmentGroup = new CustomerSegmentGroup();
        $segmentGroup->setParent(Service::createFolderByPath('/calculated-segments'));
        $segmentGroup->setPublished(true);
        $segmentGroup->setKey(\Pimcore\Model\Element\Service::getValidKey($segmentGroupReference ? : $segmentGroupName, 'object'));
        $segmentGroup->setCalculated($calculated);
        $segmentGroup->setName($segmentGroupName);
        $segmentGroup->setReference($segmentGroupReference);
        $segmentGroup->save();

        return $segmentGroup;
    }

    public function getSegmentsFromSegmentGroup(CustomerSegmentGroup $segmentGroup, array $ignoreSegments = [])
    {
        $ignoreIds = [];
        foreach($ignoreSegments as $ignoreSegment) {
            $ignoreIds[] = $ignoreSegment->getId();
        }

        $ignoreCondition = '';
        if(sizeof($ignoreIds)) {
            $ignoreCondition = " and o_id not in(" . implode($ignoreIds) . ")";
        }

        $list = new CustomerSegment\Listing;
        $list->setUnpublished(true);
        $list->setCondition("group__id = ?" . $ignoreCondition, $segmentGroup->getId());

        return $list->load();
    }

    protected function prepareSegmentBuilders(array $segmentBuilders, LoggerInterface $logger)
    {
        foreach($segmentBuilders as $segmentBuilder) {
            $logger->notice(sprintf("prepate segment builder %s", $segmentBuilder->getName()));
            $segmentBuilder->prepare($this);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param                 $segmentBuilderConfig
     *
     * @return SegmentBuilderInterface|bool
     */
    protected function createSegmentBuilder(LoggerInterface $logger, $segmentBuilderConfig)
    {

        $segmentBuilderClass = (string)$segmentBuilderConfig->segmentBuilder;
        if(class_exists($segmentBuilderConfig->segmentBuilder)) {

            if(!is_subclass_of($segmentBuilderClass, '\CustomerManagementFramework\SegmentBuilder\SegmentBuilderInterface')) {
                $logger->warning(sprintf("segment builder needs to implement SegmentBuilderInterface: %s", $segmentBuilderClass));
                return false;
            }

            try {
                $segmentBuilder = new $segmentBuilderClass($segmentBuilderConfig, $logger);

                return $segmentBuilder;
            } catch(\Exception $e) {
                $logger->warning(sprintf("segment builder could not be instanced: %s (%s)", $segmentBuilderClass, $e->getMessage()));
            }

        } else {
            $logger->warning(sprintf("segment builder not found: %s", $segmentBuilderClass));
        }
        return false;
    }

    protected function createSegmentBuilders(LoggerInterface $logger, $config) {
        $segmentBuilders = [];
        foreach($config as $segmentBuilderConfig) {
            if($segmentBuilder = self::createSegmentBuilder($logger, $segmentBuilderConfig)) {
                $segmentBuilders[] = $segmentBuilder;
            }
        }

        return $segmentBuilders;
    }
}