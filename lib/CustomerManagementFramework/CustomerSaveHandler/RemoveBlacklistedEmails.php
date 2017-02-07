<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFramework\CustomerSaveHandler;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Validator\BlacklistValidator;
use Psr\Log\LoggerInterface;

/**
 * normalizes the zip field of a given customer according to several country zip formats
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class RemoveBlacklistedEmails implements CustomerSaveHandlerInterface
{
    private $config;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;

        $this->logger = $logger;
    }


    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function process(CustomerInterface $customer)
    {
        if($this->isBlacklisted($customer->getEmail())) {
            $customer->setEmail(null);
        }
    }

    private function isBlacklisted($email) {

        $email = strtolower(trim($email));

        $validator = new \CustomerManagementFramework\DataValidator\BlacklistValidator();

        return !$validator->isValid($email);
    }
}