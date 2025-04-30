<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Model\CustomerDuplicates;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface PotentialDuplicateItemInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * @return CustomerInterface[]
     */
    public function getDuplicateCustomers();

    /**
     * @param CustomerInterface[] $duplicateCustomers
     *
     * @return void
     */
    public function setDuplicateCustomers(array $duplicateCustomers);

    /**
     * @return array
     */
    public function getFieldCombinations();

    /**
     *
     * @return void
     */
    public function setFieldCombinations(array $fieldCombinations);

    /**
     * @return bool
     */
    public function getDeclined();

    /**
     * @param bool $declined
     *
     * @return void
     */
    public function setDeclined($declined);

    /**
     * @return int
     */
    public function getModificationDate();

    /**
     * @param int $modificationDate
     *
     * @return void
     */
    public function setModificationDate($modificationDate);

    /**
     * @return int
     */
    public function getCreationDate();

    /**
     * @param int $creationDate
     *
     * @return void
     */
    public function setCreationDate($creationDate);
}
