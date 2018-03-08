<?php
/**
 * Created by PhpStorm.
 * User: dschroffner
 * Date: 05.12.2017
 * Time: 14:52
 */

namespace CustomerManagementFrameworkBundle\CustomerView\FilterDefinition;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;

class Maintenance {

    use LoggerAware;

    public function cleanUpFilterDefinitions()
    {
        $this->getLogger()->info('Start cleanup for FilterDefinition objects.');

        // fetch all FilterDefinition objects
        $filterDefinitionListing = new FilterDefinition\Listing();
        /** @var FilterDefinition[] $filterDefinitions */
        $filterDefinitions = $filterDefinitionListing->load();
        // counter for total amount of object
        $totalCounter = count($filterDefinitions);
        $this->getLogger()->info('Found FilterDefinition objects: ' . $totalCounter);
        // counter for changed object
        $changedCounter = 0;
        foreach ($filterDefinitions as $filterDefinition) {
            $changed = $filterDefinition->cleanUp();
            if($changed) $changedCounter++;
        }
        $this->getLogger()->info('Cleaned FilterDefinition objects: ' . $changedCounter);

        $this->getLogger()->info('Finished cleanup!');
    }
}