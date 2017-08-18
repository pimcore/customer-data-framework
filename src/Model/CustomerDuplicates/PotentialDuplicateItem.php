<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
