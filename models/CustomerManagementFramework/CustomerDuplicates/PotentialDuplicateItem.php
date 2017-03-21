<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-21
 * Time: 16:51
 */

namespace CustomerManagementFramework\CustomerDuplicates;

use CustomerManagementFramework\Model\CustomerInterface;

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