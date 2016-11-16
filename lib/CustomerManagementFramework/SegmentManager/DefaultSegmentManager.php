<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:02
 */

namespace CustomerManagementFramework\SegmentManager;


use CustomerManagementFramework\Helper\Objects;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use CustomerManagementFramework\SegmentBuilder\SegmentBuilderInterface;
use Pimcore\Db;
use Pimcore\File;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\Customer;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Pimcore\Model\Object\Service;
use Psr\Log\LoggerInterface;

class DefaultSegmentManager implements SegmentManagerInterface {

    CONST CHANGES_QUEUE_TABLE = 'plugin_cmf_segmentbuilder_changes_queue';

    protected $logger;

    private $config;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = $config = Plugin::getConfig()->SegmentManager;
    }


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

    public function getSegmentGroups(array $params)
    {
        $list = CustomerSegmentGroup::getList();
        $list->setUnpublished(false);

        return $list->load();
    }

    /**
     * @param array $segmentBuilderConfigs
     *
     * @return void
     */
    public function buildCalculatedSegments($changesQueueOnly = true)
    {
        $logger = $this->logger;
        $logger->notice("start segment building");

        $segmentBuilders = self::createSegmentBuilders();
        self::prepareSegmentBuilders($segmentBuilders);

        $customerList = new \Pimcore\Model\Object\Customer\Listing;

        if($changesQueueOnly) {
            $customerList->setCondition(sprintf("o_id in (select customerId from %s)", self::CHANGES_QUEUE_TABLE));
        }

        $paginator = new \Zend_Paginator($customerList);
        $paginator->setItemCountPerPage(100);

        $totalPages = $paginator->getPages()->pageCount;
        for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
            $logger->notice(sprintf("build customer segments page %s of %s", $pageNumber, $totalPages));
            $paginator->setCurrentPageNumber($pageNumber);

            foreach($paginator as $customer) {
                foreach($segmentBuilders as $segmentBuilder) {
                    $this->applySegmentBuilderToCustomer($customer, $segmentBuilder);
                }
                Db::get()->query(sprintf("delete from %s where customerId = ?", self::CHANGES_QUEUE_TABLE), $customer->getId());
            }
        }
    }

    public function buildCalculatedSegmentsOnCustomerSave(CustomerInterface $customer)
    {
        $segmentBuilders = self::createSegmentBuilders();
        self::prepareSegmentBuilders($segmentBuilders);

        foreach($segmentBuilders as $segmentBuilder) {

            if(!$segmentBuilder->executeOnCustomerSave()) {
                continue;
            }

            $this->applySegmentBuilderToCustomer($customer, $segmentBuilder);
        }
    }

    protected function applySegmentBuilderToCustomer(CustomerInterface $customer, SegmentBuilderInterface $segmentBuilder)
    {
        $this->logger->info(sprintf("apply segment builder %s to customer %s", $segmentBuilder->getName(), (string)$customer));
        $segmentBuilder->calculateSegments($customer, $this);
    }

    public function mergeCalculatedSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = [])
    {
        $currentSegments = (array)$customer->getCalculatedSegments();

        $saveNeeded = false;
        if(Objects::addObjectsToArray($currentSegments, $addSegments)) {
            $saveNeeded = true;
        }

        if(Objects::removeObjectsFromArray($currentSegments, $deleteSegments)) {
            $saveNeeded = true;
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


    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null)
    {
        $segmentGroup = self::createSegmentGroup($segmentGroup, $segmentGroup, true);

        $list = new \Pimcore\Model\Object\CustomerSegment\Listing;

        $list->setCondition("reference = ? and group__id = ? and calculated = 1", [$segmentReference, $segmentGroup->getId()]);
        $list->setUnpublished(true);
        $list->setLimit(1);
        $list = $list->load();

        if(!empty($list)) {
            return $list[0];
        }

        $segment = new CustomerSegment();
        $segment->setParent($segmentGroup);
        $segment->setKey($segmentReference);
        $segment->setName($segmentName ? : $segmentReference);
        $segment->setReference($segmentReference);
        $segment->setPublished(true);
        $segment->setCalculated(true);
        $segment->setGroup($segmentGroup);
        Objects::checkObjectKey($segment);
        $segment->save();


        return $segment;
    }

    public function createSegmentGroup($segmentGroupName, $segmentGroupReference = null, $calculated = false)
    {
        if($segmentGroup = $this->getSegmentGroupByReference($segmentGroupReference, $calculated)) {
            return $segmentGroup;
        }

        $segmentFolder = Service::createFolderByPath($calculated ? $this->config->segmentsFolder->calculated : $this->config->segmentsFolder->manual);

        $segmentGroup = new CustomerSegmentGroup();
        $segmentGroup->setParent($segmentFolder);
        $segmentGroup->setPublished(true);
        $segmentGroup->setKey($segmentGroupReference ? : $segmentGroupName);
        $segmentGroup->setCalculated($calculated);
        $segmentGroup->setName($segmentGroupName);
        $segmentGroup->setReference($segmentGroupReference);
        Objects::checkObjectKey($segmentGroup);
        $segmentGroup->save();

        return $segmentGroup;
    }

    private function getSegmentGroupByReference($segmentGroupReference, $calculated)
    {
        if(!is_null($segmentGroupReference)) {

            $list = new \Pimcore\Model\Object\CustomerSegmentGroup\Listing;
            $list->setUnpublished(true);
            $list->setCondition("reference = ? and ". ($calculated ? '(calculated = 1)' : '(calculated is null or calculated = 0)' ), $segmentGroupReference);
            $list->setUnpublished(true);
            $list->setLimit(1);
            $list = $list->load();

            return $list[0];
        }
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

    public function addCustomerToChangesQueue(CustomerInterface $customer)
    {
        Db::get()->query(sprintf("insert ignore into %s set customerId=?", self::CHANGES_QUEUE_TABLE), $customer->getId());
    }

    protected function prepareSegmentBuilders(array $segmentBuilders)
    {
        foreach($segmentBuilders as $segmentBuilder) {
            $this->logger->notice(sprintf("prepare segment builder %s", $segmentBuilder->getName()));
            $segmentBuilder->prepare($this);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param                 $segmentBuilderConfig
     *
     * @return SegmentBuilderInterface|bool
     */
    protected function createSegmentBuilder($segmentBuilderConfig)
    {
        $logger = $this->logger;

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

    /**
     * @return SegmentBuilderInterface[]|void
     */
    protected function createSegmentBuilders() {


        $config = $this->config->segmentBuilders;

        if(is_null($config)) {
            $this->logger->alert("no segmentBuilders section found in plugin config file");
            return;
        }

        if(!sizeof($config)) {
            $this->logger->alert("no segment builders defined in plugin config file");
            return;
        }

        $segmentBuilders = [];
        foreach($config as $segmentBuilderConfig) {
            if($segmentBuilder = self::createSegmentBuilder($segmentBuilderConfig)) {
                $segmentBuilders[] = $segmentBuilder;
            }
        }

        return $segmentBuilders;
    }
}