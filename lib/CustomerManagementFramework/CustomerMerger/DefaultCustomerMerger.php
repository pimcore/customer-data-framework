<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-07
 * Time: 14:45
 */

namespace CustomerManagementFramework\CustomerMerger;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Helper\Notes;
use CustomerManagementFramework\Helper\Objects;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\Service;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class DefaultCustomerMerger implements CustomerMergerInterface {

    protected $logger;
    protected $config;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;

        $config = Plugin::getConfig();
        $this->config = $config->CustomerMerger;
    }

    public function mergeCustomers(CustomerInterface $sourceCustomer, CustomerInterface $targetCustomer)
    {
        $saveValidatorBackup = Factory::getInstance()->getCustomerSaveManager()->getCustomerSaveValidatorEnabled();
        $segmentBuilderHookBackup = Factory::getInstance()->getCustomerSaveManager()->getSegmentBuildingHookEnabled();

        Factory::getInstance()->getCustomerSaveManager()->setCustomerSaveValidatorEnabled(false);
        Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled(false);

        $this->mergeCustomerValues($sourceCustomer, $targetCustomer);
        $targetCustomer->save();

        if(!$sourceCustomer->getId()) {
            $note = Notes::createNote($targetCustomer, 'cmf.CustomerMerger', "customer merged");
            $note->setDescription("merged with new customer instance");
            $note->save();
        } else {
            $note = Notes::createNote($targetCustomer, 'cmf.CustomerMerger', "customer merged");
            $note->setDescription("merged with existing customer instance");
            $note->addData("mergedCustomer", "object", $sourceCustomer);
            $note->save();

            $sourceCustomer->setParent(Service::createFolderByPath((string)$this->config->archiveDir ? (string)$this->config->archiveDir : '/customers/_archive'));
            $sourceCustomer->setPublished(false);
            $sourceCustomer->setKey($sourceCustomer->getId());
            $sourceCustomer->save();

            $note = Notes::createNote($sourceCustomer, 'cmf.CustomerMerger', "customer merged + deactivated");
            $note->addData("mergedTargetCustomer", "object", $targetCustomer);
            $note->save();

        }

        Factory::getInstance()->getCustomerSaveManager()->setCustomerSaveValidatorEnabled($saveValidatorBackup);
        Factory::getInstance()->getCustomerSaveManager()->setSegmentBuildingHookEnabled($segmentBuilderHookBackup);

        $this->logger->notice("merge customer " . $sourceCustomer . " with " . $targetCustomer);
    }

    private function mergeCustomerValues(CustomerInterface $sourceCustomer, CustomerInterface $destinationCustomer)
    {
        $class = ClassDefinition::getById($sourceCustomer::classId());

        foreach($class->getFieldDefinitions() as $fd) {
            $getter = 'get' . ucfirst($fd->getName());
            $setter = 'set' . ucfirst($fd->getName());

            if($value = $sourceCustomer->$getter()) {
                $destinationCustomer->$setter($value);
            }
        }

        $calculatedSegments = (array)$sourceCustomer->getCalculatedSegments();
        Objects::addObjectsToArray($calculatedSegments, (array)$destinationCustomer->getCalculatedSegments());
        $destinationCustomer->setCalculatedSegments($calculatedSegments);

        $manualSegments = (array)$sourceCustomer->getManualSegments();
        Objects::addObjectsToArray($manualSegments, (array)$destinationCustomer->getManualSegments());
        $destinationCustomer->setCalculatedSegments($manualSegments);

        Factory::getInstance()->getCustomerSaveManager()->setCustomerSaveValidatorEnabled(false);
    }
}