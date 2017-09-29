<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Model\AbstractObjectActivity;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\LinkActivityDefinition;

class PimcoreObjectEventListener
{
    /**
     * @var CustomerSaveManagerInterface
     */
    protected $customerSaveManager;

    public function __construct(CustomerSaveManagerInterface $customerSaveManager)
    {
        $this->customerSaveManager = $customerSaveManager;
    }

    public function onPreUpdate(ElementEventInterface $e)
    {

        if (!$e instanceof DataObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            $this->customerSaveManager->preUpdate($object);
        } elseif($object instanceof CustomerSegmentInterface) {
            \Pimcore::getContainer()->get(SegmentManagerInterface::class)->preSegmentUpdate($object);
        }
    }

    public function onPostUpdate(ElementEventInterface $e)
    {
        if (!$e instanceof DataObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if ($object instanceof CustomerInterface) {
            \Pimcore::getContainer()->get(CustomerSaveManagerInterface::class)->postUpdate($object);
        } elseif ($object instanceof AbstractObjectActivity) {
            $trackIt = true;
            if (!$object->cmfUpdateOnSave()) {
                if (\Pimcore::getContainer()->get('cmf.activity_store')->getEntryForActivity($object)) {
                    $trackIt = false;
                }
            }

            if ($trackIt) {
                \Pimcore::getContainer()->get('cmf.activity_manager')->trackActivity($object);
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
            \Pimcore::getContainer()->get('cmf.activity_manager')->deleteActivity($object);
        }
    }
}
