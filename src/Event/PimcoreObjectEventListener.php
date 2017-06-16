<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 16/06/2017
 * Time: 10:55
 */

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Event\Model\ObjectEvent;
use Pimcore\Model\Object\ActivityDefinition;

class PimcoreObjectEventListener {

    public function onPreUpdate(ElementEventInterface $e)
    {
        if(!$e instanceof ObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if($object instanceof CustomerInterface) {
            \Pimcore::getContainer()->get('cmf.customer_save_manager')->preUpdate($object);
        }
    }

    public function onPostUpdate(ElementEventInterface $e)
    {
        if(!$e instanceof ObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if($object instanceof CustomerInterface) {
            \Pimcore::getContainer()->get('cmf.customer_save_manager')->postUpdate($object);
        }
    }

    public function onPreAdd(ElementEventInterface $e)
    {
        if(!$e instanceof ObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if($object instanceof CustomerInterface) {
            \Pimcore::getContainer()->get('cmf.customer_save_manager')->preAdd($object);
        } elseif($object instanceof ActivityDefinition) {
            $object->setCode(uniqid());
        }
    }

    public function onPreDelete(ElementEventInterface $e)
    {
        if(!$e instanceof ObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if($object instanceof CustomerInterface) {
            \Pimcore::getContainer()->get('cmf.customer_save_manager')->preDelete($object);
        }
    }

    public function onPostDelete(ElementEventInterface $e)
    {
        if(!$e instanceof ObjectEvent) {
            return;
        }

        $object = $e->getObject();

        if($object instanceof CustomerInterface) {
            \Pimcore::getContainer()->get('cmf.customer_save_manager')->postDelete($object);
        }
    }
}