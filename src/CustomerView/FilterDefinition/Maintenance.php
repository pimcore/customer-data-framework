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

namespace CustomerManagementFrameworkBundle\CustomerView\FilterDefinition;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;

class Maintenance
{
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
            if ($changed) {
                $changedCounter++;
            }
        }
        $this->getLogger()->info('Cleaned FilterDefinition objects: ' . $changedCounter);

        $this->getLogger()->info('Finished cleanup!');
    }
}
