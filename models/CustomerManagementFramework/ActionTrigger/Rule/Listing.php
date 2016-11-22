<?php

namespace CustomerManagementFramework\ActionTrigger\Rule;

use CustomerManagementFramework\ActionTrigger\Rule;
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
    public function load() {
        return $this->getDao()->load();
    }
}