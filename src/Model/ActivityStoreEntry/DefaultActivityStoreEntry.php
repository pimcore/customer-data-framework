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

namespace CustomerManagementFrameworkBundle\Model\ActivityStoreEntry;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\Helper\Json;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class DefaultActivityStoreEntry implements ActivityStoreEntryInterface
{
    /**
     * @var array $data
     */
    private $data;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    /**
     * @var int|null
     */
    private $customerId;

    /**
     * @var Carbon|null
     */
    private $activityDate;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var ActivityInterface|false|null
     */
    private $relatedItem;

    /**
     * @var int|null
     */
    private $creationDate;

    /**
     * @var int|null
     */
    private $modificationDate;

    /**
     * @var string|null
     */
    private $md5;

    /**
     * @var int|null
     */
    private $o_id;

    /**
     * @var int|null
     */
    private $a_id;

    /**
     * @var string|null
     */
    private $implementationClass;

    /**
     * @var array|null
     */
    private $attributes;

    /**
     * @var array|null $metadata
     */
    private $metadata = null;

    public function setData($data)
    {
        $this->data = $data;

        $this->setId($data['id'] ?? null);
        $this->setActivityDate($data['activityDate']);
        $this->setType($data['type']);
        $this->setImplementationClass($data['implementationClass']);
        if (isset($data['attributes'])) {
            $this->setAttributes(
                is_array($data['attributes']) ? $data['attributes'] : json_decode(
                    Json::cleanUpJson($data['attributes']),
                    true
                ) ?? []
            );
        }
        $this->setMd5($data['md5'] ?? null);
        $this->setCreationDate($data['creationDate'] ?? null);
        $this->setModificationDate($data['modificationDate'] ?? null);
        $this->o_id = $data['o_id'];
        $this->a_id = $data['a_id'];
        $this->customerId = intval($data['customerId']);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        if (empty($this->customer) && $this->customerId) {
            $this->customer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($this->customerId);
        }

        return $this->customer;
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;
        $this->customerId = $customer->getId();
    }

    /**
     * @return Carbon|null
     */
    public function getActivityDate()
    {
        return $this->activityDate;
    }

    /**
     * @param int $activityDate
     */
    public function setActivityDate($activityDate)
    {
        $this->activityDate = Carbon::createFromTimestamp($activityDate);
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return ActivityInterface|false
     */
    public function getRelatedItem()
    {
        if (empty($this->relatedItem)) {
            $implementationClass = self::getImplementationClass();
            /** @var ActivityInterface $implementationClass */
            $implementationClass = \Pimcore::getContainer()->has($implementationClass) ? \Pimcore::getContainer()->has(
                $implementationClass
            ) : $implementationClass;
            $attributes = $this->getAttributes();
            $attributes['activityDate'] = $this->getActivityDate();
            $attributes['o_id'] = $this->o_id ?: $attributes['o_id'];
            $attributes['a_id'] = $this->a_id ?: $attributes['a_id'];
            $attributes['customerId'] = $this->getCustomerId();
            $this->relatedItem = $implementationClass::cmfCreate($attributes);
        }

        return $this->relatedItem;
    }

    /**
     * @param ActivityInterface $relatedItem
     */
    public function setRelatedItem(ActivityInterface $relatedItem)
    {
        $this->relatedItem = $relatedItem;
    }

    /**
     * @return int|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int|null
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return string|null
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @param string $md5
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;
    }

    /**
     * @return string|null
     */
    public function getImplementationClass()
    {
        return $this->implementationClass;
    }

    /**
     * @param string $implementationClass
     */
    public function setImplementationClass($implementationClass)
    {
        $this->implementationClass = $implementationClass;
    }

    /**
     * @return array|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getData()
    {
        $data = $this->data;
        $data['id'] = $this->getId();
        $data['customerId'] = $this->customerId;
        $data['activityDate'] = $this->getActivityDate()->getTimestamp();
        $data['type'] = $this->getType();
        $data['implementationClass'] = $this->getImplementationClass();
        $data['attributes'] = $this->getAttributes();
        $data['md5'] = $this->getMd5();
        $data['creationDate'] = $this->getCreationDate();
        $data['modificationDate'] = $this->getModificationDate();
        $data['o_id'] = $this->o_id;
        $data['a_id'] = $this->a_id;

        return $data;
    }

    public function save($updateAttributes = false)
    {
        $relatedItem = $this->getRelatedItem();
        if ($relatedItem) {
            $relatedItem->setCustomer($this->getCustomer());
        }

        $this->getActivityStore()->updateActivityStoreEntry($this, $relatedItem ? $updateAttributes : false);
    }

    public function getMetadata(): array
    {
        $this->loadMetadata();

        return (array) $this->metadata;
    }

    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadataItem($key)
    {
        $this->loadMetadata();

        return $this->metadata[$key] ?? null;
    }

    public function setMetadataItem($key, $data)
    {
        $this->loadMetadata();
        $this->metadata[$key] = $data;
    }

    /**
     * metadata is lazy loaded
     */
    private function loadMetadata()
    {
        if (is_null($this->metadata)) {
            $this->getActivityStore()->lazyLoadMetadataOfEntry($this);
        }
    }

    private function getActivityStore(): ActivityStoreInterface
    {
        return \Pimcore::getContainer()->get('cmf.activity_store');
    }
}
