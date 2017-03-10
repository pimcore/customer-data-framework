<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-10
 * Time: 16:22
 */

namespace CustomerManagementFramework\CustomerProvider\ObjectNamingScheme;

use CustomerManagementFramework\Model\CustomerInterface;

interface ObjectNamingSchemeInterface
{
    /**
     * @param CustomerInterface $customer
     * @param string $parentPath
     * @param string $namingScheme
     * @return void
     */
    public function apply(CustomerInterface $customer, $parentPath, $namingScheme);
}