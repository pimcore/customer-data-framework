<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFramework\DataTransformer\CustomerDataTransformer;

use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface CustomerDataTransformerInterface
{
    public function __construct($config, LoggerInterface $logger);

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function transform(CustomerInterface $customer);
}