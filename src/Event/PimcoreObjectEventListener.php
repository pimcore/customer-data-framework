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

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Model\AbstractObjectActivity;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\DataObjectImportEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\LinkActivityDefinition;

class PimcoreObjectEventListener
{
    public function __construct(
        protected CustomerSaveManagerInterface $customerSaveManager,
        protected SegmentManagerInterface $segmentManager,
        protected ActivityManagerInterface $activityManager,
        protected ActivityStoreInterface $activityStore
    )
    {
    }

    public function onPreUpdate(ElementEventInterface $e)
    {
        //do not update index when auto save or only saving version or when $e not DataObjectEvent
        if (($e->hasArgument('isAutoSave') && $e->getArgument('isAutoSave')) ||
            ($e->hasArgument('saveVersionOnly') && $e->getArgument('saveVersionOnly')) ||
            !$e instanceof DataObjectEvent) {
            return;
        }

        //do not call customerSaveManager on recyclebin restore
        if ($e->hasArgument('isRecycleBinRestore') && $e->getArgument('isRecycleBinRestore')) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            $this->customerSaveManager->preUpdate($object);
        } elseif ($object instanceof CustomerSegmentInterface) {
            $this->segmentManager->preSegmentUpdate($object);
        }
    }

    public function onPostUpdate(ElementEventInterface $e)
    {
        // Do not process the event any further in either cases of autoSave, save version only or the event not being an instance of DataObject
        if (($e->hasArgument('isAutoSave') && $e->getArgument('isAutoSave')) ||
            ($e->hasArgument('saveVersionOnly') && $e->getArgument('saveVersionOnly')) ||
            !$e instanceof DataObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            $this->customerSaveManager->postUpdate($object);
        } elseif ($object instanceof AbstractObjectActivity) {
            $trackIt = true;
            if (!$object->cmfUpdateOnSave()) {
                if ($this->activityStore->getEntryForActivity($object)) {
                    $trackIt = false;
                }
            }

            if ($trackIt) {
                $this->activityManager->trackActivity($object);
            }
        }
    }

    public function onPreAdd(ElementEventInterface $e)
    {
        if (!$e instanceof DataObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            $this->customerSaveManager->preAdd($object);
        } elseif ($object instanceof LinkActivityDefinition) {
            $object->setCode(uniqid());
        }
    }

    public function onPostAdd(ElementEventInterface $e)
    {
        if (!$e instanceof DataObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            $this->customerSaveManager->postAdd($object);
        }
    }

    public function onPreDelete(ElementEventInterface $e)
    {
        if (!$e instanceof DataObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            $this->customerSaveManager->preDelete($object);
        }
    }

    public function onPostDelete(ElementEventInterface $e)
    {
        if (!$e instanceof DataObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            $this->customerSaveManager->postDelete($object);
        } elseif ($object instanceof ActivityInterface) {
            $this->activityManager->deleteActivity($object);
        } elseif ($object instanceof CustomerSegmentInterface) {
            $this->segmentManager->postSegmentDelete($object);
        }
    }

    public function onPreSave(DataObjectImportEvent $e)
    {
        $data = $e->getAdditionalData();

        $customer = $e->getObject();

        if (!$customer instanceof CustomerInterface) {
            return;
        }

        if ($data) {
            /**
             * check to be compatible with different Pimcore versions
             */
            if (is_array($data)) {
                $data = $data['customerSegmentId'];
            }

            if ($segment = $this->segmentManager->getSegmentById($data)) {
                $this->segmentManager->mergeSegments($customer, [$segment], [], 'Customer CSV importer');
            }
        }
    }
}
