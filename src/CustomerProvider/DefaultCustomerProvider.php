<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerProvider;

use CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme\ObjectNamingSchemeInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject\Concrete;

class DefaultCustomerProvider implements CustomerProviderInterface
{
    /**
     * @var string
     */
    protected $pimcoreClass;

    /**
     * @var string
     */
    protected $parentPath;

    /**
     * @var ObjectNamingSchemeInterface
     */
    protected $namingScheme;

    /**
     * DefaultCustomerProvider constructor.
     *
     * @param string $pimcoreClass
     * @param string $parentPath
     * @param ObjectNamingSchemeInterface $namingScheme
     */
    public function __construct($pimcoreClass, $parentPath, ObjectNamingSchemeInterface $namingScheme)
    {
        $this->pimcoreClass = $pimcoreClass;
        if (empty($this->pimcoreClass)) {
            throw new \RuntimeException('Customer class is not defined');
        }

        $this->parentPath = $parentPath;

        if (empty($this->parentPath)) {
            throw new \RuntimeException('Customer save path is not defined');
        }

        $this->namingScheme = $namingScheme;
    }

    /**
     * @return string
     */
    protected function getDiClassName()
    {
        return sprintf('Pimcore\Model\DataObject\%s', $this->pimcoreClass);
    }

    /**
     * @return string
     */
    protected function getDiListingClassName()
    {
        return sprintf('Pimcore\Model\DataObject\%s\Listing', $this->pimcoreClass);
    }

    /**
     * @return int
     */
    public function getCustomerClassId()
    {
        return $this->callStatic('classId');
    }

    /**
     * @return string
     */
    public function getCustomerClassName()
    {
        $class = $this->getDiClassName();
        $customer = new $class;

        return get_class($customer);
    }

    /**
     * Get an object listing
     *
     * @return \Pimcore\Model\DataObject\Listing\Concrete
     */
    public function getList()
    {
        $listClass = $this->getDiListingClassName();

        return new $listClass();
    }

    /**
     * Create a customer instance
     *
     * @param array $data
     *
     * @return CustomerInterface
     */
    public function create(array $data = [])
    {
        $className = $this->getDiClassName();

        /** @var CustomerInterface|ElementInterface|Concrete $customer */
        $customer = new $className;
        $customer->setPublished(true);
        $customer->setValues($data);
        $this->applyObjectNamingScheme($customer);

        return $customer;
    }

    /**
     * @param CustomerInterface|ElementInterface|Concrete $customer
     * @param array $data
     *
     * @return CustomerInterface
     */
    public function update(CustomerInterface $customer, array $data = [])
    {
        // TODO naive version - add validation / settable values
        $customer->setValues($data);

        return $customer;
    }

    /**
     * @param CustomerInterface|ElementInterface|Concrete $customer
     *
     * @return $this
     */
    public function delete(CustomerInterface $customer)
    {
        $customer->delete();

        return $this;
    }

    /**
     * Get customer by ID
     *
     * @param int $id
     * @param bool $force
     *
     * @return CustomerInterface|null
     */
    public function getById($id, $force = false)
    {
        return $this->callStatic('getById', [$id, $force]);
    }

    /**
     * Get active customer by email
     *
     * @param int $id
     * @param bool $foce
     *
     * @return CustomerInterface|null
     *
     * @throws \RuntimeException
     */
    public function getActiveCustomerByEmail($email)
    {
        $list = $this->getList();
        $list->setUnpublished(false);
        $list->addConditionParam('active = 1 and trim(lower(email)) = ?', [$email]);

        if ($list->count() > 1) {
            throw new \Exception(sprintf('multiple active and published customers with email %s found', $email));
        }

        return $list->current();
    }

    /**
     * Sets the correct parent folder and object key for the given customer.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function applyObjectNamingScheme(CustomerInterface $customer)
    {
        $this->namingScheme->apply($customer);
    }

    public function getParentParentPath()
    {
        return $this->parentPath;
    }

    public function setParentPath($parentPath)
    {
        $this->parentPath = $parentPath;
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    protected function callStatic($method, array $arguments = [])
    {
        $className = $this->getDiClassName();

        return call_user_func_array([$className, $method], $arguments);
    }
}
