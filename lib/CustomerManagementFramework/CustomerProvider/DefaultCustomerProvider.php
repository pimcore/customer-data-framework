<?php

namespace CustomerManagementFramework\CustomerProvider;

use CustomerManagementFramework\CustomerProvider\ObjectNamingScheme\ObjectNamingSchemeInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\Folder;

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

    protected $namingScheme;

    public function __construct()
    {
        $this->pimcoreClass = Plugin::getConfig()->General->CustomerPimcoreClass;
        if (empty($this->pimcoreClass)) {
            throw new \RuntimeException('Customer class is not defined');
        }

        $config = Plugin::getConfig()->CustomerProvider;
        $this->parentPath = $config->parentPath;

        if (empty($this->parentPath)) {
            throw new \RuntimeException('Customer save path is not defined');
        }

        $this->namingScheme = $config->namingScheme;
    }

    /**
     * @return string
     */
    protected function getDiClassName()
    {
        return sprintf('Pimcore\Model\Object\%s', $this->pimcoreClass);
    }

    /**
     * @return string
     */
    protected function getDiListingClassName()
    {
        return sprintf('Pimcore\Model\Object\%s\Listing', $this->pimcoreClass);
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
        return get_class(\Pimcore::getDiContainer()->make($this->getDiClassName()));
    }

    /**
     * Get an object listing
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getList()
    {
        return \Pimcore::getDiContainer()->make($this->getDiListingClassName());
    }

    /**
     * Create a customer instance
     *
     * @param array $data
     * @return CustomerInterface
     */
    public function create(array $data = [])
    {

        /** @var CustomerInterface|ElementInterface|Concrete $customer */
        $customer = \Pimcore::getDiContainer()->make($this->getDiClassName());
        $customer->setPublished(true);
        $customer->setValues($data);
        $this->applyObjectNamingScheme($customer);

        return $customer;
    }

    /**
     * @param CustomerInterface|ElementInterface|Concrete $customer
     * @param array $data
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
     * @return CustomerInterface|null
     */
    public function getById($id)
    {
        return $this->callStatic('getById', [$id]);
    }

    /**
     * Sets the correct parent folder and object key for the given customer.
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function applyObjectNamingScheme(CustomerInterface $customer)
    {
        $namingScheme = \Pimcore::getDiContainer()->get(ObjectNamingSchemeInterface::class);
        $namingScheme->apply($customer, $this->parentPath, $this->namingScheme);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    protected function callStatic($method, array $arguments = [])
    {
        $className = $this->getDiClassName();

        return call_user_func_array([$className, $method], $arguments);
    }
}
