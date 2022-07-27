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

namespace CustomerManagementFrameworkBundle\Newsletter\Queue\Item;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class DefaultNewsletterQueueItem implements NewsletterQueueItemInterface
{
    /**
     * @var int
     */
    private $customerId;

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string
     */
    private $operation;

    /**
     * Could be used as a storage for an overruled operation.
     * If an update item is in the queue but the related customer is excluded by $customer->needsExportByNewsletterProviderHandler($providerHandler)
     * the customer needs to be deleted allthough it is an update operation in the queue.
     *
     * @var string|null
     */
    private $overruledOperation;

    /**
     * @var int|null
     */
    private $modificationDate;

    /**
     * @var bool
     */
    private $successfullyProcessed = false;

    public function __construct($customerId, CustomerInterface $customer = null, $email, $operation, $modificationDate = null)
    {
        $modificationDate = !is_null($modificationDate) ? $modificationDate : round(microtime(true) * 1000);

        $this->customerId = $customerId;
        $this->customer = $customer;
        $this->email = $email;
        $this->operation = $operation;
        $this->modificationDate = $modificationDate;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @return bool
     */
    public function wasSuccessfullyProcessed()
    {
        return $this->successfullyProcessed;
    }

    /**
     * @param bool $successfullyProcessed
     */
    public function setSuccessfullyProcessed($successfullyProcessed)
    {
        $this->successfullyProcessed = $successfullyProcessed;
    }

    /**
     * Could be used as a storage for an overruled operation.
     * If an update item is in the queue but the related customer is excluded by $customer->needsExportByNewsletterProviderHandler($providerHandler)
     * the customer needs to be deleted allthough it is an update operation in the queue.
     *
     * @return string
     */
    public function getOverruledOperation()
    {
        return $this->overruledOperation;
    }

    /**
     * Could be used as a storage for an overruled operation.
     * If an update item is in the queue but the related customer is excluded by $customer->needsExportByNewsletterProviderHandler($providerHandler)
     * the customer needs to be deleted allthough it is an update operation in the queue.
     *
     * @param string|null $overruledOperation
     */
    public function setOverruledOperation($overruledOperation)
    {
        $this->overruledOperation = $overruledOperation;
    }

    public function toJson()
    {
        return json_encode([
            'customerId' => $this->customerId,
            'operation' => $this->operation,
            'email' => $this->email,
            'modificationDate' => $this->modificationDate
        ]);
    }
}
