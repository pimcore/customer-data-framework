<?php
namespace CustomerManagementFramework\CustomerProvider;

use CustomerManagementFramework\Model\CustomerInterface;

interface CustomerProviderInterface
{
    /**
     * Get an object listing
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getList();

    /**
     * Create a customer instance
     *
     * @param string $key
     * @param array $values
     * @return CustomerInterface
     */
    public function create($key, array $values = []);

    /**
     * Get customer by ID
     *
     * @param int $id
     * @return CustomerInterface|null
     */
    public function getById($id);
}
