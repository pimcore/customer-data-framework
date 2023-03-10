<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\CustomerMerger;

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Helper\Notes;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\DataObject\ClassDefinition;

class DefaultCustomerMerger implements CustomerMergerInterface
{
    use LoggerAware;

    /**
     * @var CustomerSaveManagerInterface
     */
    protected $customerSaveManager;

    public function __construct(CustomerSaveManagerInterface $customerSaveManager)
    {
        $this->customerSaveManager = $customerSaveManager;
    }

    /**
     * Adds all values from source customer to target customer and returns merged target customer instance.
     * Afterwards the source customer will be set to inactive and unpublished.
     *
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     * @param bool $mergeAttributes
     *
     * @return CustomerInterface
     */
    public function mergeCustomers(
        CustomerInterface $sourceCustomer,
        CustomerInterface $targetCustomer,
        $mergeAttributes = true
    ) {

        //backup save options
        $saveOptions = $this->customerSaveManager->getSaveOptions(true);

        // disable unneeded components
        $this->customerSaveManager->getSaveOptions()
            ->disableValidator()
            ->disableOnSaveSegmentBuilders();

        $this->mergeCustomerValues($sourceCustomer, $targetCustomer, $mergeAttributes);
        $targetCustomer->save();

        if (!$sourceCustomer->getId()) {
            $note = Notes::createNote($targetCustomer, 'cmf.CustomerMerger', 'customer merged');
            $note->setDescription('merged with new customer instance');
            $note->save();
        } else {
            $note = Notes::createNote($targetCustomer, 'cmf.CustomerMerger', 'customer merged');
            $note->setDescription('merged with existing customer instance');
            $note->addData('mergedCustomer', 'object', $sourceCustomer);
            $note->save();

            $sourceCustomer->setPublished(false);
            $sourceCustomer->setActive(false);

            $sourceCustomer->save();

            $note = Notes::createNote($sourceCustomer, 'cmf.CustomerMerger', 'customer merged + deactivated');
            $note->addData('mergedTargetCustomer', 'object', $targetCustomer);
            $note->save();
        }

        // restore save options
        $this->customerSaveManager->setSaveOptions($saveOptions);

        $logAddon = '';
        if (!$mergeAttributes) {
            $logAddon .= ' (attributes merged manually)';
        }

        $this->getLogger()->notice('merge customer '.$sourceCustomer.' with '.$targetCustomer.$logAddon);

        return $targetCustomer;
    }

    /**
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     * @param bool $mergeAttributes
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

        if ($sourceCustomer->getId()) {
            $this->mergeActivities($sourceCustomer, $targetCustomer);
        }
    }

    /**
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     */
    private function mergeActivities(CustomerInterface $sourceCustomer, CustomerInterface $targetCustomer)
    {
        $list = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityList();
        $list->setCondition('customerId='.$sourceCustomer->getId());
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
