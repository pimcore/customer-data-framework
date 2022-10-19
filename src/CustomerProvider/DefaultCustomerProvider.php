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

namespace CustomerManagementFrameworkBundle\CustomerProvider;

use CustomerManagementFrameworkBundle\CustomerProvider\Exception\DuplicateCustomersFoundException;
use CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme\ObjectNamingSchemeInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\Concrete;
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
     * @var bool
     */
    protected $usesClassOverride = false;

    /**
     * @var null|string
     */
    protected $classNameWithoutNamespace = null;

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

        if (!class_exists($pimcoreClass)) {
            if (!class_exists('Pimcore\\Model\\DataObject\\' . $pimcoreClass)) {
                throw new \RuntimeException(sprintf('Configured CMF customer data object class "%s" does not exist.', $pimcoreClass));
            }
        } else {
            $this->usesClassOverride = true;

            // Get ending of classname for listings.
            $explodedClassName = explode('\\', $pimcoreClass);
            $this->classNameWithoutNamespace = end($explodedClassName);
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
        if ($this->usesClassOverride) {
            return $this->pimcoreClass;
        }

        return sprintf('Pimcore\Model\DataObject\%s', $this->pimcoreClass);
    }

    /**
     * @return string
     */
    protected function getDiListingClassName()
    {
        if ($this->usesClassOverride) {
            // Check if Listing class exists in default namespace
            return sprintf('Pimcore\Model\DataObject\%s\Listing', $this->classNameWithoutNamespace);
        }

        return sprintf('Pimcore\Model\DataObject\%s\Listing', $this->pimcoreClass);
    }

    /**
     * @return string
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
        /** @var \Pimcore\Model\DataObject\Listing\Concrete $listClass */
        $listClass = $this->modelFactory->build($listClass);

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
        $customer = $this->createCustomerInstance();
        $customer->setValues($data);
        $customer->setPublished(true);
        $this->applyObjectNamingScheme($customer);

        return $customer;
    }

    /**
     * Create a customer instance
     *
     * @return Concrete&CustomerInterface
     */
    public function createCustomerInstance()
    {
        $className = $this->getDiClassName();

        /** @var Concrete&CustomerInterface $customer */
        $customer = $this->modelFactory->build($className);

        return $customer;
    }

    /**
     * @param CustomerInterface $customer
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
     * @param CustomerInterface $customer
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
        return $this->callStatic('getById', [$id, ['force' => $force]]);
    }

    /**
     * Get active customer by email
     *
     * @param string $email
     *
     * @return CustomerInterface|null
     *
     * @throws DuplicateCustomersFoundException
     */
    public function getActiveCustomerByEmail($email)
    {
        if (!trim($email)) {
            return null;
        }

        $list = $this->getList();
        $list->setUnpublished(false);
        $this->addActiveCondition($list);
        $list->addConditionParam('trim(email) like ?', [$list->escapeLike(trim($email))]);

        if ($list->count() > 1) {
            throw new DuplicateCustomersFoundException(sprintf('multiple active and published customers with email %s found', $email));
        }

        /** @var CustomerInterface $customer */
        $customer = $list->current();

        return $customer;
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

    public function getParentPath()
    {
        return $this->parentPath;
    }

    /**
     * @return string
     *
     * @deprecated use getParentPath() instead.
     */
    public function getParentParentPath()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '3.3.0',
            'The DefaultCustomerProvider::getParentParentPath() method is deprecated, use DefaultCustomerProvider::getParentPath() instead.'
        );

        return $this->getParentPath();
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

    /**
     * @param \Pimcore\Model\DataObject\Listing\Concrete $list
     */
    public function addActiveCondition($list)
    {
        $list->addConditionParam('active = 1');
    }

    /**
     * @param \Pimcore\Model\DataObject\Listing\Concrete $list
     */
    public function addInActiveCondition($list)
    {
        $list->addConditionParam('(active IS NULL OR active != 1)');
    }
}
