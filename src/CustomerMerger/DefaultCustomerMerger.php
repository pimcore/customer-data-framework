<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-07
 * Time: 14:45
 */

namespace CustomerManagementFrameworkBundle\CustomerMerger;

use CustomerManagementFrameworkBundle\ActionTrigger\Condition\Customer;
use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Helper\Notes;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Plugin;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\Service;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class DefaultCustomerMerger implements CustomerMergerInterface
{

    use LoggerAware;

    protected $config;

    public function __construct()
    {

        $config = Config::getConfig();
        $this->config = $config->CustomerMerger;
    }

    /**
     * Adds all values from source customer to target customer and returns merged target customer instance.
     * Afterwards the source customer will be set to inactive and unpublished.
     *
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     * @param bool $mergeAttributes
     * @return CustomerInterface
     */
    public function mergeCustomers(
        CustomerInterface $sourceCustomer,
        CustomerInterface $targetCustomer,
        $mergeAttributes = true
    ) {
        $saveValidatorBackup = \Pimcore::getContainer()->get(
            'cmf.customer_save_manager'
        )->getCustomerSaveValidatorEnabled();
        $segmentBuilderHookBackup = \Pimcore::getContainer()->get(
            'cmf.customer_save_manager'
        )->getSegmentBuildingHookEnabled();

        \Pimcore::getContainer()->get('cmf.customer_save_manager')->setCustomerSaveValidatorEnabled(false);
        \Pimcore::getContainer()->get('cmf.customer_save_manager')->setSegmentBuildingHookEnabled(false);

        $this->mergeCustomerValues($sourceCustomer, $targetCustomer, $mergeAttributes);
        $targetCustomer->save();

        if (!$sourceCustomer->getId()) {
            $note = Notes::createNote($targetCustomer, 'cmf.CustomerMerger', "customer merged");
            $note->setDescription("merged with new customer instance");
            $note->save();
        } else {
            $note = Notes::createNote($targetCustomer, 'cmf.CustomerMerger', "customer merged");
            $note->setDescription("merged with existing customer instance");
            $note->addData("mergedCustomer", "object", $sourceCustomer);
            $note->save();

            $sourceCustomer->setParent(
                Service::createFolderByPath(
                    (string)$this->config->archiveDir ? (string)$this->config->archiveDir : '/customers/_archive'
                )
            );
            $sourceCustomer->setPublished(false);
            $sourceCustomer->setActive(false);
            $sourceCustomer->setKey($sourceCustomer->getId());
            $sourceCustomer->save();

            $note = Notes::createNote($sourceCustomer, 'cmf.CustomerMerger', "customer merged + deactivated");
            $note->addData("mergedTargetCustomer", "object", $targetCustomer);
            $note->save();
        }

        \Pimcore::getContainer()->get('cmf.customer_save_manager')->setCustomerSaveValidatorEnabled(
            $saveValidatorBackup
        );
        \Pimcore::getContainer()->get('cmf.customer_save_manager')->setSegmentBuildingHookEnabled(
            $segmentBuilderHookBackup
        );

        $logAddon = '';
        if (!$mergeAttributes) {
            $logAddon .= ' (attributes merged manually)';
        }

        $this->getLogger()->notice("merge customer ".$sourceCustomer." with ".$targetCustomer.$logAddon);

        return $targetCustomer;
    }

    /**
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     * @param $mergeAttributes
     */
    private function mergeCustomerValues(
        CustomerInterface $sourceCustomer,
        CustomerInterface $targetCustomer,
        $mergeAttributes
    ) {
        if ($mergeAttributes) {
            $class = ClassDefinition::getById($sourceCustomer::classId());

            foreach ($class->getFieldDefinitions() as $fd) {
                $getter = 'get'.ucfirst($fd->getName());
                $setter = 'set'.ucfirst($fd->getName());

                if ($value = $sourceCustomer->$getter()) {
                    $targetCustomer->$setter($value);
                }
            }
        }

        $calculatedSegments = (array)$sourceCustomer->getCalculatedSegments();
        Objects::addObjectsToArray($calculatedSegments, (array)$targetCustomer->getCalculatedSegments());
        $targetCustomer->setCalculatedSegments($calculatedSegments);

        $manualSegments = (array)$sourceCustomer->getManualSegments();
        Objects::addObjectsToArray($manualSegments, (array)$targetCustomer->getManualSegments());
        $targetCustomer->setManualSegments($manualSegments);

        \Pimcore::getContainer()->get('cmf.customer_save_manager')->setCustomerSaveValidatorEnabled(false);

        $this->mergeActivities($sourceCustomer, $targetCustomer);
    }

    /**
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     */
    private function mergeActivities(CustomerInterface $sourceCustomer, CustomerInterface $targetCustomer)
    {
        $list = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityList();
        $list->setCondition("customerId=".$sourceCustomer->getId());
        $list->setOrderKey('activityDate');
        $list->setOrder('desc');

        /**
         * @var ActivityStoreEntryInterface $item
         */
        foreach ($list as $item) {
            $item->setCustomer($targetCustomer);
            $item->save();
        }
    }
}