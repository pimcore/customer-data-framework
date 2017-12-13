<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\CustomerProvider;

use CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme\ObjectNamingSchemeInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Factory;

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
     * @var Factory
     */
    protected $modelFactory;

    /**
     * DefaultCustomerProvider constructor.
     *
     * @param string $pimcoreClass
     * @param string $parentPath
     * @param ObjectNamingSchemeInterface $namingScheme
     */
    public function __construct($pimcoreClass, $parentPath, ObjectNamingSchemeInterface $namingScheme, Factory $modelFactory)
    {
        $this->pimcoreClass = $pimcoreClass;
        if (empty($this->pimcoreClass)) {
            throw new \RuntimeException('Customer class is not defined');
        }

        if(!class_exists('Pimcore\\Model\\DataObject\\' . $pimcoreClass)) {
            throw new \RuntimeException(sprintf('Configured CMF customer data object class "%s" does not exist.', $pimcoreClass));
        }

        $this->parentPath = $parentPath;

        if (empty($this->parentPath)) {
            throw new \RuntimeException('Customer save path is not defined');
        }

        $this->namingScheme = $namingScheme;

        $this->modelFactory = $modelFactory;
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
        $customer = $this->modelFactory->build($class);

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
        /** @var CustomerInterface|ElementInterface|Concrete $customer */
        $customer = $this->createCustomerInstance();
        $customer->setValues($data);
        $customer->setPublished(true);
        $this->applyObjectNamingScheme($customer);

        return $customer;
    }

    /**
     * Create a customer instance
     *
     * @return CustomerInterface
     */
    public function createCustomerInstance()
    {
        $className = $this->getDiClassName();

        /** @var CustomerInterface|ElementInterface|Concrete $customer */
        $customer = $this->modelFactory->build($className);
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
