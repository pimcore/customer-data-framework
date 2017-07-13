<?php

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;

use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use Pimcore\Model\Listing\AbstractListing;

class Listing extends AbstractListing
{
    public function isValidOrderKey($key)
    {
        return true;
    }

    /**
     * @return Rule[]
     */
    public function load()
    {
        return $this->getDao()->load();
    }
}