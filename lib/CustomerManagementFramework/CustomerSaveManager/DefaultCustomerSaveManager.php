<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:10
 */

namespace CustomerManagementFramework\CustomerSaveManager;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

class DefaultCustomerSaveManager implements CustomerSaveManagerInterface
{

    public function __construct(LoggerInterface $logger)
    {

    }

    public function preUpdate(CustomerInterface $customer)
    {
        $this->applyDataTransformers($customer);
    }

    public function postUpdate(CustomerInterface $customer)
    {
        $this->applyDataTransformers($customer);

        Factory::getInstance()->getSegmentManager()->buildCalculatedSegmentsOnCustomerSave($customer);
        Factory::getInstance()->getSegmentManager()->addCustomerToChangesQueue($customer);
    }

    public function applyDataTransformers(CustomerInterface $customer)
    {

    }

}