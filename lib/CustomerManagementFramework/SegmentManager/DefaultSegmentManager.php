<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:02
 */

namespace CustomerManagementFramework\SegmentManager;


use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Helper\Objects;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\Plugin;
use CustomerManagementFramework\SegmentBuilder\SegmentBuilderInterface;
use Pimcore\Db;
use Pimcore\File;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Concrete;
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

        $backup = Factory::getInstance()->getCustomerSaveManager()->getSegmentBuildingHookEnabled();
        Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled(false);

        $segmentBuilders = self::createSegmentBuilders();
        self::prepareSegmentBuilders($segmentBuilders);

        $customerList = Factory::getInstance()->getCustomerProvider()->getList();

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
                    try {
                        $this->applySegmentBuilderToCustomer($customer, $segmentBuilder);
                    } catch(\Exception $e) {
                        $this->logger->error($e);
                    }

                }
                Db::get()->query(sprintf("delete from %s where customerId = ?", self::CHANGES_QUEUE_TABLE), $customer->getId());
            }
        }

        Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled($backup);
    }

    public function buildCalculatedSegmentsOnCustomerSave(CustomerInterface $customer)
    {
        $segmentBuilders = self::createSegmentBuilders();
        self::prepareSegmentBuilders($segmentBuilders, true);

        foreach($segmentBuilders as $segmentBuilder) {

            if(!$segmentBuilder->executeOnCustomerSave()) {
                continue;
            }

            $this->applySegmentBuilderToCustomer($customer, $segmentBuilder);
        }
    }

    public function executeSegmentBuilderMaintenance()
    {
        $segmentBuilders = self::createSegmentBuilders();

        foreach($segmentBuilders as $segmentBuilder) {
            $segmentBuilder->maintenance($this);
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
            $segmentBuildingHookBackup = Factory::getInstance()->getCustomerSaveManager()->getSegmentBuildingHookEnabled();
            Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled(false);
            $customer->save();
            Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled($segmentBuildingHookBackup);

            if(!$backup) {
                \Pimcore\Model\Version::enable();
            }
        }
    }

    public function mergeManualSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = [])
    {
        $currentSegments = (array)$customer->getManualSegments();

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
            $customer->setManualSegments($currentSegments);
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
        $segment->setKey(Objects::getValidKey($segmentReference));
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
            $ignoreCondition = " and o_id not in(" . implode(',', $ignoreIds) . ")";
        }

        $list = new CustomerSegment\Listing;
        $list->setUnpublished(true);
        $list->setCondition("group__id = ?" . $ignoreCondition, $segmentGroup->getId());

        $result = $list->load();

        return $result ? : [];
    }

    public function addCustomerToChangesQueue(CustomerInterface $customer)
    {
        Db::get()->query(sprintf("insert ignore into %s set customerId=?", self::CHANGES_QUEUE_TABLE), $customer->getId());
    }


    public function preSegmentUpdate(CustomerSegmentInterface $segment)
    {
        if($segment instanceof Concrete) {

            $parent = $segment;

            $group = null;
            while($parent) {
                $parent = $parent->getParent();

                if($parent instanceof CustomerSegmentGroup) {
                    $group = $parent;
                    break;
                }
            }

            if($group) {
                $segment->setGroup($parent);
            } else {
                $segment->setGroup(null);
            }
        }
    }

    public function customerHasSegment(CustomerInterface $customer, CustomerSegmentInterface $segment)
    {
        if($segments = $customer->getAllSegments()) {
            foreach($segments as $s) {
                if($s->getId() == $segment->getId()) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * @param SegmentBuilderInterface[] $segmentBuilders
     * @param bool  $ignoreAsyncSegmentBuilders
     */
    protected function prepareSegmentBuilders(array $segmentBuilders, $ignoreAsyncSegmentBuilders = false)
    {
        foreach($segmentBuilders as $segmentBuilder) {

            if($ignoreAsyncSegmentBuilders && !$segmentBuilder->executeOnCustomerSave()) {
                continue;
            }

            $this->logger->notice(sprintf("prepare segment builder %s", $segmentBuilder->getName()));
            $segmentBuilder->prepare($this);
        }
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
            $segmentBuilders[] = Factory::getInstance()->createObject((string)$segmentBuilderConfig->segmentBuilder, SegmentBuilderInterface::class, [$segmentBuilderConfig, $this->logger]);
        }

        return $segmentBuilders;
    }
}