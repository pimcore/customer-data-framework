<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\SegmentManager;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentBuilder\SegmentBuilderInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Pimcore\Model\Object\Service;

class DefaultSegmentManager implements SegmentManagerInterface
{
    use LoggerAware;

    /**
     * @var string|\Pimcore\Model\Object\Folder
     */
    protected $segmentFolderCalculated;

    /**
     * @var string|\Pimcore\Model\Object\Folder
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
     * DefaultSegmentManager constructor.
     * @param $segmentFolderCalculated
     * @param $segmentFolderManual
     * @param CustomerSaveManagerInterface $customerSaveManager
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct($segmentFolderCalculated, $segmentFolderManual, CustomerSaveManagerInterface $customerSaveManager, CustomerProviderInterface $customerProvider)
    {
        $this->segmentFolderCalculated = $segmentFolderCalculated;
        $this->segmentFolderManual = $segmentFolderManual;

        $this->customerSaveManager = $customerSaveManager;
        $this->customerProvider = $customerProvider;
    }

    /**
     * @param int $id
     *
     * @return CustomerSegmentInterface
     */
    public function getSegmentById($id)
    {
        return CustomerSegment::getById($id);
    }

    /**
     * @param int $id
     *
     * @return CustomerSegmentGroup
     */
    public function getSegmentGroupById($id)
    {
        return CustomerSegmentGroup::getById($id);
    }

    /**
     * @param array $segmentIds
     * @param string $conditionMode
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND)
    {
        $list = $this->customerProvider->getList();
        $list->setUnpublished(false);

        $conditions = [];
        foreach ($segmentIds as $segmentId) {
            $conditions[] = '(o_id in (select src_id from object_relations_1 where dest_id = ' . intval($segmentId) . '))';
        }

        if (sizeof($conditions)) {
            $list->setCondition('(' . implode(' ' . $conditionMode . ' ', $conditions) . ')');
        }

        return $list;
    }

    /**
     * @param array $params
     *
     * @return CustomerSegment\Listing
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
     * @param array $params
     *
     * @return CustomerSegmentGroup\Listing
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
     * @param bool $calculated
     * @return \Pimcore\Model\Object\Folder
     */
    public function getSegmentsFolder($calculated = true)
    {
        $folder = $calculated ? $this->segmentFolderCalculated : $this->segmentFolderManual;

        if(is_string($folder)) {
            $folder = Service::createFolderByPath($folder);
        }

        return $folder;
    }

    /**
     * @param                      $segmentReference
     * @param CustomerSegmentGroup $segmentGroup
     * @param null $calculated
     *
     * @return CustomerSegment|null
     * @throws \RuntimeException
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

        if($segmentGroup) {
            $list->addConditionParam('group__id = ?', $segmentGroup->getId());
        }

        if($list->count() > 1) {
            throw new \RuntimeException(
                sprintf('Ambiguous results: found more than one segment with reference %s', $segmentReference)
            );
        }

        return $list->current();
    }

    /**
     * @param string $segmentName
     * @param CustomerSegmentGroup|string $segmentGroup
     * @param null $segmentReference
     * @param bool $calculated
     * @param null $subFolder
     *
     * @return mixed|CustomerSegment
     *
     * @throws \RuntimeException
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
     * @param string $segmentReference
     * @param CustomerSegmentGroup|string $segmentGroup
     * @param null $segmentName
     * @param null $subFolder
     *
     * @return mixed|CustomerSegment
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
     * @param       $segmentGroupName
     * @param null $segmentGroupReference
     * @param bool $calculated
     * @param array $values
     *
     * @return CustomerSegmentGroup
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
     * @param CustomerSegmentGroup $segmentGroup
     * @param array $values
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
     * @param CustomerSegment $segment
     * @param array $values
     *
     * @throws \Exception
     */
    public function updateSegment(CustomerSegment $segment, array $values = [])
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
     * @param $segmentGroupReference
     * @param $calculated
     *
     * @return CustomerSegmentGroup|null
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

        if($list->count() > 1) {
            throw new \RuntimeException(
                sprintf('Ambiguous results: found more than one segment group with reference %s', $segmentGroupReference)
            );
        }

        return $list->current();
    }

    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param array $ignoreSegments
     *
     * @return CustomerSegment[]
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
     * @param CustomerSegmentInterface $segment
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
     * @param CustomerInterface $customer
     * @param CustomerSegmentInterface $segment
     *
     * @return bool
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
     * Return segments of given customers which are within given customer segment group.
     *
     * @param CustomerInterface $customer
     * @param CustomerSegmentGroup|string $group
     *
     * @return CustomerSegmentInterface[]
     */
    public function getCustomersSegmentsFromGroup(CustomerInterface $customer, $group)
    {
        if(!$group instanceof CustomerSegmentGroup) {
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
        $hintForNotes = null
    ) {
        \Pimcore::getContainer()->get('cmf.segment_manager.segment_merger')->mergeSegments(
            $customer,
            $addSegments,
            $deleteSegments,
            $hintForNotes
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
     * @return SegmentBuilderInterface[]
     */
    public function getSegmentBuilders()
    {
        return $this->segmentBuilders;
    }
}
