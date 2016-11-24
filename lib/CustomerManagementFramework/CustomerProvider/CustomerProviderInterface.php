<?php
namespace CustomerManagementFramework\CustomerProvider;

use CustomerManagementFramework\Model\CustomerInterface;

interface CustomerProviderInterface
{
    /**
     * @return int
     */
    public function getCustomerClassId();

    /**
     * @return string
     */
    public function getCustomerClassName();

    /**
     * Get an object listing
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getList();

    /**
     * Create a customer instance
     *
     * @param array $values
     * @return CustomerInterface
     */
    public function create(array $values = []);

    /**
     * Get customer by ID
     *
     * @param int $id
     * @return CustomerInterface|null
     */
    public function getById($id);
}
