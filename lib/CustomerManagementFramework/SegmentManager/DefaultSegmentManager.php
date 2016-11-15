<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:02
 */

namespace CustomerManagementFramework\SegmentManager;


use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\Plugin;
use CustomerManagementFramework\SegmentBuilder\SegmentBuilderInterface;
use Pimcore\File;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\Customer;
use Pimcore\Model\Object\Service;
use Pimcore\Version;
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

        foreach($config as $segmentBuilderConfig) {

            if($segmentBuilder = self::createSegmentBuilder($logger, $segmentBuilderConfig)) {

                $logger->notice(sprintf("start segment builder %s",$segmentBuilderConfig->segmentBuilder));
                $customerList = $segmentBuilder->prepare();

                if(!$customerList instanceof \Pimcore\Model\Object\Customer\Listing) {
                    $logger->error(sprintf("segment builder %s prepare() method needs to return a customer list",$segmentBuilderConfig->segmentBuilder));
                    continue;
                }

                $this->buildSegments($logger, $customerList, $segmentBuilder);
            }
        }
    }

    protected function buildSegments(LoggerInterface $logger, \Pimcore\Model\Object\Customer\Listing $customerList, SegmentBuilderInterface $segmentBuilder) {

        $paginator = new \Zend_Paginator($customerList);
        $paginator->setItemCountPerPage(100);

        $totalPages = $paginator->getPages()->pageCount;
        for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
            $logger->notice(sprintf("build segments page %s of %s (%s)", $pageNumber, $totalPages, $segmentBuilder->getName()));
            $paginator->setCurrentPageNumber($pageNumber);
            
            foreach($paginator as $customer) {
                $logger->info(sprintf("apply segment builder %s to customer %s", $segmentBuilder->getName(), (string)$customer));

                $segmentBuilder->calculateSegments($customer, $this);
                exit;

            }
        }
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
    public function createCalculatedSegment($segmentReference, $segmentGroup = null, $segmentName = null)
    {
        $list = new \Pimcore\Model\Object\CustomerSegment\Listing;

        $list->setCondition("reference = ?", $segmentReference);
        $list->setUnpublished(true);
        $list->setLimit(1);
        $list = $list->load();

        if($segment = $list[0]) {
            return $segment;
        }

        $segment = new CustomerSegment();

        $folderName = '/calculated-segments';
        if(!is_null($segmentGroup)) {
            $folderName .= '/' . \Pimcore\Model\Element\Service::getValidKey($segmentGroup, 'object');
        }

        $segment->setParent(\Pimcore\Model\Object\Service::createFolderByPath($folderName));
        $segment->setKey(\Pimcore\Model\Element\Service::getValidKey($segmentReference, 'object'));
        $segment->setName($segmentName ? : $segmentReference);
        $segment->setReference($segmentReference);
        $segment->setPublished(true);
        $segment->save();
        return $segment;
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
}