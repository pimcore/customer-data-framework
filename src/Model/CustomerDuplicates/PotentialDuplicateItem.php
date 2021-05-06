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

namespace CustomerManagementFrameworkBundle\Model\CustomerDuplicates;

class PotentialDuplicateItem implements PotentialDuplicateItemInterface
{
    private $id;
    private $duplicateCustomers;
    private $fieldCombinations;
    private $declined;
    private $modificationDate;
    private $creationDate;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDuplicateCustomers()
    {
        return $this->duplicateCustomers;
    }

    public function setDuplicateCustomers(array $duplicateCustomers)
    {
        $this->duplicateCustomers = $duplicateCustomers;
    }

    public function getFieldCombinations()
    {
        return $this->fieldCombinations;
    }

    public function setFieldCombinations(array $fieldCombinations)
    {
        $this->fieldCombinations = $fieldCombinations;
    }

    public function getDeclined()
    {
        return $this->declined;
    }

    public function setDeclined($declined)
    {
        $this->declined = $declined;
    }

    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
