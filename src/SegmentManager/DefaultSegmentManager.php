<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\SegmentManager;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\StoredFunctions\StoredFunctionsInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;
use CustomerManagementFrameworkBundle\SegmentBuilder\SegmentBuilderInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\Element\ElementInterface;

class DefaultSegmentManager implements SegmentManagerInterface
{
    use LoggerAware;

    /**
     * @var string|\Pimcore\Model\DataObject\Folder
     */
    protected $segmentFolderCalculated;

    /**
     * @var string|\Pimcore\Model\DataObject\Folder
     */
    protected $segmentFolderManual;

    /**
     * @var CustomerSaveManagerInterface
     */
    protected $customerSaveManager;

    /**
     * @var SegmentBuilderInterface[]
     */
    protected $segmentBuilders = [];

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * maps actual types of elements implementing ElementInterface to type strings used with db tables
     *
     * @var TypeMapperInterface
     */
    protected $typeMapper = null;

    /**
     * @var StoredFunctionsInterface
     */
    protected $storedFunctions = null;

    /**
     * @param $segmentFolderCalculated
     * @param $segmentFolderManual
     * @param CustomerSaveManagerInterface $customerSaveManager
     * @param CustomerProviderInterface $customerProvider
     * @param TypeMapperInterface $typeMapper
     * @param StoredFunctionsInterface $storedFunctions
     */
    public function __construct($segmentFolderCalculated, $segmentFolderManual, CustomerSaveManagerInterface $customerSaveManager, CustomerProviderInterface $customerProvider, TypeMapperInterface $typeMapper, StoredFunctionsInterface $storedFunctions)
    {
        $this->segmentFolderCalculated = $segmentFolderCalculated;
        $this->segmentFolderManual = $segmentFolderManual;

        $this->customerSaveManager = $customerSaveManager;
        $this->customerProvider = $customerProvider;

        $this->setTypeMapper($typeMapper);
        $this->setStoredFunctions($storedFunctions);
    }

    /**
     * @return TypeMapperInterface
     */
    public function getTypeMapper(): TypeMapperInterface
    {
        return $this->typeMapper;
    }

    /**
     * @param TypeMapperInterface $typeMapper
     */
    public function setTypeMapper(TypeMapperInterface $typeMapper)
    {
        $this->typeMapper = $typeMapper;
    }

    /**
     * @return StoredFunctionsInterface
     */
    public function getStoredFunctions(): StoredFunctionsInterface {
        return $this->storedFunctions;
    }

    /**
     * @param StoredFunctionsInterface $storedFunctions
     */
    public function setStoredFunctions(StoredFunctionsInterface $storedFunctions) {
        $this->storedFunctions = $storedFunctions;
    }

    /**
     * @inheritdoc
     */
    public function getSegmentById($id)
    {
        return CustomerSegment::getById($id);
    }

    /**
     * @inheritdoc
     */
    public function getSegmentGroupById($id)
    {
        return CustomerSegmentGroup::getById($id);
    }

    /**
     * @inheritdoc
     */
    public function getSegmentsForElement(ElementInterface $element): array
    {
        $id = $element->getId();
        $type = $this->getTypeMapper()->getTypeStringByObject($element);

        return $this->getSegmentsForElementId($id, $type);
    }

    /**
     * @inheritdoc
     */
    public function getSegmentsForElementId(string $id, string $type): array
    {
        $segmentIds = $this->getStoredFunctions()->retrieve($id, $type);

        $segments = array_map(function (string $id) {
            return CustomerSegment::getById($id);
        }, $segmentIds);

        return array_filter($segments);
    }

    /**
     * @inheritdoc
     */
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND)
    {
        $list = $this->customerProvider->getList();
        $list->setUnpublished(false);

        $conditions = [];
        foreach ($segmentIds as $segmentId) {
            $conditions[] = '(o_id in (select distinct src_id from object_relations_' . $this->customerProvider->getCustomerClassId() . ' where (fieldname = "manualSegments" or fieldname = "calculatedSegments") and dest_id = ' . intval($segmentId) . '))';
        }

        if (sizeof($conditions)) {
            $list->setCondition('(' . implode(' ' . $conditionMode . ' ', $conditions) . ')');
        }

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function getSegments(array $params = [])
    {
        /**
         * @var CustomerSegment\Listing $list;
         */
        $list = CustomerSegment::getList();
        $list->setUnpublished(false);

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function getSegmentGroups()
    {
        /**
         * @var CustomerSegmentGroup\Listing $list;
         */
        $list = CustomerSegmentGroup::getList();
        $list->setUnpublished(false);

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function getSegmentsFolder($calculated = true)
    {
        $folder = $calculated ? $this->segmentFolderCalculated : $this->segmentFolderManual;

        if (is_object($folder)) {
            return $folder;
        }

        $folder = Service::createFolderByPath($folder);

        if ($calculated) {
            $this->segmentFolderCalculated = $folder;
        } else {
            $this->segmentFolderManual = $folder;
        }

        return $folder;
    }

    /**
     * @inheritdoc
     */
    public function getSegmentByReference($segmentReference, CustomerSegmentGroup $segmentGroup = null, $calculated = null)
    {
        $list = $this->getSegments()
            ->setUnpublished(true)
            ->addConditionParam('reference = ?', $segmentReference);

        if (!is_null($calculated)) {
            if ($calculated) {
                $list->addConditionParam('calculated = 1');
            } else {
                $list->addConditionParam('(calculated is null or calculated = 0)');
            }
        }

        if ($segmentGroup) {
            $list->addConditionParam('group__id = ?', $segmentGroup->getId());
        }

        if ($list->count() > 1) {
            throw new \RuntimeException(
                sprintf('Ambiguous results: found more than one segment with reference %s', $segmentReference)
            );
        }

        return $list->current();
    }

    /**
     * @inheritdoc
     */
    public function createSegment(
        $segmentName,
        $segmentGroup,
        $segmentReference = null,
        $calculated = true,
        $subFolder = null
    ) {
        if ($segmentGroup instanceof CustomerSegmentGroup && $segmentGroup->getCalculated() != $calculated) {
            throw new \RuntimeException(
                sprintf(
                    "it's not possible to create a %s segment within a %s segment group",
                    $calculated ? 'calculated' : 'manual',
                    $calculated ? 'manual' : 'calculated'
                )
            );
        }

        $segmentGroup = self::createSegmentGroup($segmentGroup, $segmentGroup, $calculated);

        if ($segment = $this->getSegmentByReference($segmentReference, $segmentGroup)) {
            return $segment;
        }

        $parent = $segmentGroup;
        if (!is_null($subFolder)) {
            $subFolder = explode('/', $subFolder);
            $folder = [];
            foreach ($subFolder as $f) {
                if ($f = Objects::getValidKey($f)) {
                    $folder[] = $f;
                }
            }
            $subFolder = implode('/', $folder);

            if ($subFolder) {
                $fullPath = str_replace('//', '/', $segmentGroup->getFullPath().'/'.$subFolder);
                $parent = Service::createFolderByPath($fullPath);
            }
        }

        $segment = new CustomerSegment();
        $segment->setParent($parent);
        $segment->setKey(Objects::getValidKey($segmentReference ?: $segmentName));
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
     * @inheritdoc
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null, $subFolder = null)
    {
        return $this->createSegment(
            $segmentName ?: $segmentReference,
            $segmentGroup,
            $segmentReference,
            true,
            $subFolder
        );
    }

    /**
     * @inheritdoc
     */
    public function createSegmentGroup(
        $segmentGroupName,
        $segmentGroupReference = null,
        $calculated = true,
        array $values = []
    ) {
        if ($segmentGroupName instanceof CustomerSegmentGroup) {
            return $segmentGroupName;
        }

        if ($segmentGroup = $this->getSegmentGroupByReference($segmentGroupReference, $calculated)) {
            return $segmentGroup;
        }

        $segmentGroup = new CustomerSegmentGroup();
        $segmentGroup->setParent($this->getSegmentsFolder($calculated));
        $segmentGroup->setPublished(true);
        $segmentGroup->setKey(Objects::getValidKey($segmentGroupReference ?: $segmentGroupName));
        $segmentGroup->setCalculated($calculated);
        $segmentGroup->setName($segmentGroupName);
        $segmentGroup->setReference($segmentGroupReference);

        $segmentGroup->setValues($values);

        Objects::checkObjectKey($segmentGroup);
        $segmentGroup->save();

        return $segmentGroup;
    }

    /**
     * @inheritdoc
     */
    public function updateSegmentGroup(CustomerSegmentGroup $segmentGroup, array $values = [])
    {
        $currentCalculatedState = $segmentGroup->getCalculated();
        $segmentGroup->setValues($values);
        $segmentGroup->setKey($segmentGroup->getReference() ?: $segmentGroup->getName());
        Objects::checkObjectKey($segmentGroup);

        if (isset($values['calculated'])) {
            $newCalculatedState = (bool)$values['calculated'];
            if ($newCalculatedState != $currentCalculatedState) {
                foreach ($this->getSegmentsFromSegmentGroup($segmentGroup) as $segment) {
                    if ($segment->getCalculated() != $newCalculatedState) {
                        $segment->setCalculated($newCalculatedState);
                        $segment->save();
                    }
                }

                $segmentGroup->setParent($this->getSegmentsFolder($newCalculatedState));
            }
        }

        $segmentGroup->save();
    }

    /**
     * @inheritdoc
     */
    public function updateSegment(CustomerSegmentInterface $segment, array $values = [])
    {
        $segment->setValues($values);

        if (!empty($values['group'])) {
            if (!$segmentGroup = CustomerSegmentGroup::getById($values['group'])) {
                throw new \Exception('SegmentGroup with id %s not found', $values['group']);
            }

            $segment->setGroup($segmentGroup);
            $segment->setParent($segmentGroup);
        }

        if (isset($values['calculated']) && $group = $segment->getGroup()) {
            if ($group->getCalculated() != (bool)$values['calculated']) {
                throw new \Exception("calculated state of segment cannot be different then for it's segment group");
            }
        }

        $segment->setKey($segment->getReference() ?: $segment->getName());
        Objects::checkObjectKey($segment);
        $segment->save();
    }

    /**
     * @inheritdoc
     */
    public function getSegmentGroupByReference($segmentGroupReference, $calculated)
    {
        if (is_null($segmentGroupReference)) {
            return null;
        }

        $list = $this->getSegmentGroups()
            ->setUnpublished(true)
            ->setCondition(
                'reference = ? and '.($calculated ? '(calculated = 1)' : '(calculated is null or calculated = 0)'),
                $segmentGroupReference
            );

        if ($list->count() > 1) {
            throw new \RuntimeException(
                sprintf('Ambiguous results: found more than one segment group with reference %s', $segmentGroupReference)
            );
        }

        return $list->current();
    }

    /**
     * @inheritdoc
     */
    public function getSegmentsFromSegmentGroup(CustomerSegmentGroup $segmentGroup, array $ignoreSegments = [])
    {
        $list = $this->getSegments()
            ->addConditionParam('group__id = ?', $segmentGroup->getId())
            ->setOrderKey('name');

        $ignoreIds = Objects::getIdsFromArray($ignoreSegments);

        if (sizeof($ignoreIds)) {
            $list->addConditionParam('o_id not in(' . implode(',', $ignoreIds) . ')');
        }

        $result = $list->load();

        return $result ?: [];
    }

    /**
     * @inheritdoc
     */
    public function preSegmentUpdate(CustomerSegmentInterface $segment)
    {
        if ($segment instanceof Concrete) {
            $parent = $segment;

            $segment->setGroup(null);
            while ($parent) {
                $parent = $parent->getParent();

                if ($parent instanceof CustomerSegmentGroup) {
                    $segment->setGroup($parent);

                    return;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function customerHasSegment(CustomerInterface $customer, CustomerSegmentInterface $segment)
    {
        foreach ($customer->getAllSegments() as $s) {
            if ($s->getId() == $segment->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getCalculatedSegmentsFromCustomer(CustomerInterface $customer)
    {
        return $this->extractSegmentsFromPimcoreFieldData($customer->getCalculatedSegments());
    }

    /**
     * @inheritdoc
     */
    public function getManualSegmentsFromCustomer(CustomerInterface $customer)
    {
        return $this->extractSegmentsFromPimcoreFieldData($customer->getManualSegments());
    }

    /**
     * The CMF supports object with metadata and "normal" object relations as store for the segments of a customer.
     * This methods extracts the segments if object with metadata is used.
     *
     * @param CustomerSegmentInterface[]|ObjectMetadata[]|null $segments
     *
     * @return CustomerSegmentInterface[]
     */
    protected function extractSegmentsFromPimcoreFieldData($segments)
    {
        if (!is_array($segments)) {
            return [];
        }

        if (!sizeof($segments)) {
            return [];
        }

        $result = [];
        foreach ($segments as $segment) {
            if ($segment instanceof CustomerSegmentInterface) {
                $result[] = $segment;
            } elseif ($segment instanceof ObjectMetadata && $segment->getObject() instanceof CustomerSegmentInterface) {
                $result[] = $segment->getObject();
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getCustomersSegmentsFromGroup(CustomerInterface $customer, $group)
    {
        if (!$group instanceof CustomerSegmentGroup) {
            $group = $this->getSegmentGroupByReference($group, true);
        }

        if (!$group instanceof CustomerSegmentGroup) {
            return [];
        }

        if (!$segments = $customer->getAllSegments()) {
            return [];
        }

        $result = [];
        foreach ($segments as $segment) {
            if ($segment->getGroup() && $segment->getGroup()->getId() == $group->getId()) {
                $result[] = $segment;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function mergeSegments(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments = [],
        $hintForNotes = null,
        $segmentCreatedTimestamp = null,
        $segmentApplicationCounter = null
    ) {
        \Pimcore::getContainer()->get('cmf.segment_manager.segment_merger')->mergeSegments(
            $customer,
            $addSegments,
            $deleteSegments,
            $hintForNotes,
            $segmentCreatedTimestamp,
            $segmentApplicationCounter
        );
    }

    /**
     * @inheritdoc
     */
    public function saveMergedSegments(CustomerInterface $customer)
    {
        \Pimcore::getContainer()->get('cmf.segment_manager.segment_merger')->saveMergedSegments($customer);
    }

    public function addSegmentBuilder(SegmentBuilderInterface $segmentBuilder)
    {
        $this->segmentBuilders[] = $segmentBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getSegmentBuilders()
    {
        return $this->segmentBuilders;
    }
}
