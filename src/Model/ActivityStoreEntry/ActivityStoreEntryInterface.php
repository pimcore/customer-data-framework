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

namespace CustomerManagementFrameworkBundle\Model\ActivityStoreEntry;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface ActivityStoreEntryInterface
{
    /**
     * @param array $data
     */
    public function setData($data);

    public function save($updateAttributes = false);

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * @return CustomerInterface
     */
    public function getCustomer();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function setCustomer(CustomerInterface $customer);

    /**
     * @return Carbon
     */
    public function getActivityDate();

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function setActivityDate($timestamp);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType($type);

    /**
     * @return ActivityInterface|false
     */
    public function getRelatedItem();

    /**
     * @param ActivityInterface $item
     *
     * @return void
     */
    public function setRelatedItem(ActivityInterface $item);

    /**
     * @return int
     */
    public function getCreationDate();

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function setCreationDate($timestamp);

    /**
     * @return int
     */
    public function getModificationDate();

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function setModificationDate($timestamp);

    /**
     * @return string
     */
    public function getMd5();

    /**
     * @param string $md5
     *
     * @return void
     */
    public function setMd5($md5);

    /**
     * @return string
     */
    public function getImplementationClass();

    /**
     * @param string $implementationClass
     *
     * @return void
     */
    public function setImplementationClass($implementationClass);

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return array
     */
    public function getMetadata(): array;

    /**
     * @param array $metadata
     *
     * @return void
     */
    public function setMetadata(array $metadata);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getMetadataItem($key);

    /**
     * @param string $key
     * @param mixed $data
     *
     * @return void;
     */
    public function setMetadataItem($key, $data);
}
