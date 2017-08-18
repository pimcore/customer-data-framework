<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;

class AfterTrackActivity extends AbstractSingleCustomerEvent
{
    /**
     * @var ActivityInterface $activity
     */
    private $activity;

    /** @var ActivityStoreEntryInterface */
    private $entry;

    /**
     * @return ActivityInterface
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param ActivityInterface $activity
     */
    public function setActivity(ActivityInterface $activity)
    {
        $this->activity = $activity;
    }

    /**
     * @return ActivityStoreEntryInterface
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param ActivityStoreEntryInterface $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }

    public function getName()
    {
        return 'plugin.cmf.after-track-activity';
    }
}
