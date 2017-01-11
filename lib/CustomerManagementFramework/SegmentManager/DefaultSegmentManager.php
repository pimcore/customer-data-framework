<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:02
 */

namespace CustomerManagementFramework\SegmentManager;


use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Helper\Notes;
use CustomerManagementFramework\Helper\Objects;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\Plugin;
use CustomerManagementFramework\SegmentBuilder\SegmentBuilderInterface;
use Pimcore\Db;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\CustomerSegment;
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

    /**
     * @param int $id
     * @return CustomerSegmentInterface
     */
    public function getSegmentById($id)
    {
        return CustomerSegment::getById($id);
    }

    /**
     * @param int $id
     * @return CustomerSegmentGroup
     */
    public function getSegmentGroupById($id)
    {
        return CustomerSegmentGroup::getById($id);
    }

    /**
     * @param array  $segmentIds
     * @param string $conditionMode
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND)
    {
        $list = Factory::getInstance()->getCustomerProvider()->getList();
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

    /**
     * @param array $params
     *
     * @return CustomerSegment[]
     */
    public function getSegments(array $params = [])
    {
        $list = CustomerSegment::getList();
        $list->setUnpublished(false);

        return $list->load();
    }

    /**
     * @param array $params
     *
     * @return CustomerSegmentGroup[]
     */
    public function getSegmentGroups(array $params = [])
    {
        $list = CustomerSegmentGroup::getList();
        $list->setUnpublished(false);

        return $list->load();
    }

    /**
     * @param bool $changesQueueOnly
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

                $event = new \CustomerManagementFramework\ActionTrigger\Event\ExecuteSegmentBuilders($customer);

                \Pimcore::getEventManager()->trigger($event->getName(), $event);

                Db::get()->query(sprintf("delete from %s where customerId = ?", self::CHANGES_QUEUE_TABLE), $customer->getId());
            }
        }

        Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled($backup);
    }

    /**
     * @param CustomerInterface $customer
     */
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

    /**
     *
     */
    public function executeSegmentBuilderMaintenance()
    {
        $segmentBuilders = self::createSegmentBuilders();

        foreach($segmentBuilders as $segmentBuilder) {
            $segmentBuilder->maintenance($this);
        }
    }

    /**
     * @param CustomerInterface       $customer
     * @param SegmentBuilderInterface $segmentBuilder
     */
    protected function applySegmentBuilderToCustomer(CustomerInterface $customer, SegmentBuilderInterface $segmentBuilder)
    {
        $this->logger->info(sprintf("apply segment builder %s to customer %s", $segmentBuilder->getName(), (string)$customer));
        $segmentBuilder->calculateSegments($customer, $this);
    }

    /**
     * @param CustomerInterface $customer
     * @param array             $addSegments
     * @param array             $deleteSegments
     * @param string|null       $hintForNotes
     *
     * @return mixed
     */
    public function mergeSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = [], $hintForNotes = null)
    {
        $addCalculatedSegments = [];
        foreach($addSegments as $segment) {
            if($segment->getCalculated()) {
                $addCalculatedSegments[] = $segment;
            }
        }
        $deleteCalculatedSegments = [];
        foreach($deleteSegments as $segment) {
            if($segment->getCalculated()) {
                $deleteCalculatedSegments[] = $segment;
            }
        }

        if(sizeof($addCalculatedSegments) || sizeof($deleteCalculatedSegments)) {
            $this->mergeSegmentsHelper($customer, $addCalculatedSegments, $deleteCalculatedSegments, true, $hintForNotes);
        }

        $addManualSegments = [];
        foreach($addSegments as $segment) {
            if(!$segment->getCalculated()) {
                $addManualSegments[] = $segment;
            }
        }
        $deleteManualSegments = [];
        foreach($deleteSegments as $segment) {
            if(!$segment->getCalculated()) {
                $deleteManualSegments[] = $segment;
            }
        }

        if(sizeof($addManualSegments) || sizeof($deleteManualSegments)) {
            $this->mergeSegmentsHelper($customer, $addManualSegments, $deleteManualSegments, false, $hintForNotes);
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param array             $addSegments
     * @param array             $deleteSegments
     * @param bool              $calculated
     * @param                   $hintForNotes
     */
    protected function mergeSegmentsHelper(CustomerInterface $customer, array $addSegments, array $deleteSegments = [], $calculated = false, $hintForNotes)
    {
        $currentSegments = $calculated ? (array)$customer->getCalculatedSegments() : (array)$customer->getManualSegments();

        $saveNeeded = false;
        if($addedSegments = Objects::addObjectsToArray($currentSegments, $addSegments)) {
            $saveNeeded = true;
        }

        if($removedSegments = Objects::removeObjectsFromArray($currentSegments, $deleteSegments)) {
            $saveNeeded = true;
        }

        if($saveNeeded) {
            $backup = \Pimcore\Model\Version::$disabled;
            \Pimcore\Model\Version::disable();
            if($calculated) {
                $customer->setCalculatedSegments($currentSegments);
            } else {
                $customer->setManualSegments($currentSegments);
            }

            $segmentBuildingHookBackup = Factory::getInstance()->getCustomerSaveManager()->getSegmentBuildingHookEnabled();
            Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled(false);
            $customer->save();
            Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled($segmentBuildingHookBackup);

            if(is_array($addedSegments) && sizeof($addedSegments)) {

                $description = [];

                $title = 'Segment(s) added';
                if($hintForNotes) {
                    $title .= ' (' . $hintForNotes . ')';
                }

                $note = Notes::createNote($customer, 'cmf.SegmentManager', $title);
                $i = 0;
                foreach ($addedSegments as $segment) {
                    $i++;
                    $note->addData("segment" . $i, "object", $segment);
                    $description[] = $segment;
                }
                $note->setDescription(implode(', ', $addedSegments));

                $note->save();
            }

            if(is_array($removedSegments) && sizeof($removedSegments)) {

                $description = [];

                $title = 'Segment(s) removed';
                if($hintForNotes) {
                    $title .= ' (' . $hintForNotes . ')';
                }

                $note = Notes::createNote($customer, 'cmf.SegmentManager', $title);
                $i = 0;
                foreach ($removedSegments as $segment) {
                    $i++;
                    $note->addData("segment" . $i, "object", $segment);
                    $description[] = $segment;
                }
                $note->setDescription(implode(', ', $description));

                $note->save();
            }

            if(!$backup) {
                \Pimcore\Model\Version::enable();
            }
        }
    }

    /**
     * @param                      $segmentReference
     * @param CustomerSegmentGroup $segmentGroup
     * @param null                 $calculated
     *
     * @return mixed
     */
    public function getSegmentByReference($segmentReference, CustomerSegmentGroup $segmentGroup, $calculated = null) {


        $list = new \Pimcore\Model\Object\CustomerSegment\Listing;

        $calculatedCondition = '';
        if(!is_null($calculated)) {
            $calculatedCondition = "and calculated = 1";
            if(!$calculated) {
                $calculatedCondition = "and (calculated is null or calculated = 0)";
            }
        }

        $list->setCondition("reference = ? and group__id = ? " . $calculatedCondition, [$segmentReference, $segmentGroup->getId()]);
        $list->setUnpublished(true);
        $list->setLimit(1);
        $list = $list->load();

        if(!empty($list)) {
            return $list[0];
        }
    }

    /**
     * @param string                      $segmentName
     * @param CustomerSegmentGroup|string $segmentGroup
     * @param null                        $segmentReference
     * @param bool                        $calculated
     * @param null                        $subFolder
     *
     * @return mixed|CustomerSegment
     * @throws \Exception
     */
    public function createSegment($segmentName, $segmentGroup, $segmentReference = null, $calculated = true, $subFolder = null){

        if($segmentGroup instanceof CustomerSegmentGroup && $segmentGroup->getCalculated() != $calculated) {
            throw new \Exception(sprintf("it's not possible to create a %s segment within a %s segment group",
                $calculated ? 'calculated' : 'manual',
                $calculated ? 'manual' : 'calculated')
            );
        }

        $segmentGroup = self::createSegmentGroup($segmentGroup, $segmentGroup, $calculated);

        if($segment = $this->getSegmentByReference($segmentReference, $segmentGroup)) {
            return $segment;
        }

        $parent = $segmentGroup;
        if(!is_null($subFolder)) {
            $subFolder = explode('/', $subFolder);
            $folder = [];
            foreach($subFolder as $f) {
                if($f = Objects::getValidKey($f)) {
                    $folder[] = $f;
                }
            }
            $subFolder = implode('/', $folder);

            if($subFolder) {
                $fullPath = str_replace('//', '/', $segmentGroup->getFullPath() . '/' . $subFolder);
                $parent = Service::createFolderByPath($fullPath);
            }
        }

        $segment = new CustomerSegment();
        $segment->setParent($parent);
        $segment->setKey(Objects::getValidKey($segmentReference ? : $segmentName));
        $segment->setName($segmentName);
        $segment->setReference($segmentReference);
        $segment->setPublished(true);
        $segment->setCalculated($calculated);
        $segment->setGroup($segmentGroup);
        Objects::checkObjectKey($segment);
        $segment->save();


        return $segment;
    }

    /**
     * @param string                      $segmentReference
     * @param CustomerSegmentGroup|string $segmentGroup
     * @param null                        $segmentName
     * @param null                        $subFolder
     *
     * @return mixed|CustomerSegment
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null, $subFolder = null)
    {
        return $this->createSegment($segmentName ? : $segmentReference, $segmentGroup, $segmentReference, true, $subFolder);
    }

    /**
     * @param       $segmentGroupName
     * @param null  $segmentGroupReference
     * @param bool  $calculated
     * @param array $values
     *
     * @return CustomerSegmentGroup
     */
    public function createSegmentGroup($segmentGroupName, $segmentGroupReference = null, $calculated = false, array $values = [])
    {
        if($segmentGroupName instanceof CustomerSegmentGroup) {
            return $segmentGroupName;
        }

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

        $segmentGroup->setValues($values);

        Objects::checkObjectKey($segmentGroup);
        $segmentGroup->save();

        return $segmentGroup;
    }

    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param array                $values
     */
    public function updateSegmentGroup(CustomerSegmentGroup $segmentGroup, array $values = [])
    {
        $calculatedState = $segmentGroup->getCalculated();
        $segmentGroup->setValues($values);
        $segmentGroup->setKey(Objects::getValidKey($segmentGroup->getReference() ? : $segmentGroup->getName()));
        Objects::checkObjectKey($segmentGroup);

        if(isset($values['calculated'])) {
            if((bool)$values['calculated'] != $calculatedState) {
                foreach(Factory::getInstance()->getSegmentManager()->getSegmentsFromSegmentGroup($segmentGroup) as $segment) {
                    if($segment->getCalculated() != (bool)$values['calculated']) {
                        $segment->setCalculated((bool)$values['calculated']);
                        $segment->save();
                    }
                }

                $segmentGroup->setParent(Service::createFolderByPath((bool)$values['calculated'] ? $this->config->segmentsFolder->calculated : $this->config->segmentsFolder->manual));
            }
        }

        $segmentGroup->save();
    }

    /**
     * @param CustomerSegment $segment
     * @param array           $values
     *
     * @throws \Exception
     */
    public function updateSegment(CustomerSegment $segment, array $values = [])
    {


        $segment->setValues($values);

        if(!empty($values['group'])) {
            if(!$segmentGroup = CustomerSegmentGroup::getById($values['group'])) {
                throw new \Exception("SegmentGroup with id %s not found", $values['group']);
            }

            $segment->setGroup($segmentGroup);
            $segment->setParent($segmentGroup);
        }

        if(isset($values['calculated']) && $group = $segment->getGroup()) {
            if($group->getCalculated() != (bool)$values['calculated']) {
                throw new \Exception("calculated state of segment cannot be different then for it's segment group");
            }
        }

        $segment->setKey(Objects::getValidKey($segment->getReference() ? : $segment->getName()));
        Objects::checkObjectKey($segment);

        $segment->save();
    }

    /**
     * @param $segmentGroupReference
     * @param $calculated
     *
     * @return mixed
     */
    public function getSegmentGroupByReference($segmentGroupReference, $calculated)
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

    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param array                $ignoreSegments
     *
     * @return array
     */
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

    /**
     * @param CustomerInterface $customer
     */
    public function addCustomerToChangesQueue(CustomerInterface $customer)
    {
        Db::get()->query(sprintf("insert ignore into %s set customerId=?", self::CHANGES_QUEUE_TABLE), $customer->getId());
    }


    /**
     * @param CustomerSegmentInterface $segment
     */
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

    /**
     * @param CustomerInterface        $customer
     * @param CustomerSegmentInterface $segment
     *
     * @return bool
     */
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
     * @return SegmentBuilderInterface[]|null
     */
    protected function createSegmentBuilders() {


        $config = $this->config->segmentBuilders;

        if(is_null($config)) {
            $this->logger->alert("no segmentBuilders section found in plugin config file");
            return null;
        }

        if(!sizeof($config)) {
            $this->logger->alert("no segment builders defined in plugin config file");
            return null;
        }

        $segmentBuilders = [];
        foreach($config as $segmentBuilderConfig) {
            $segmentBuilders[] = Factory::getInstance()->createObject((string)$segmentBuilderConfig->segmentBuilder, SegmentBuilderInterface::class, [$segmentBuilderConfig, $this->logger]);
        }

        return $segmentBuilders;
    }
}
