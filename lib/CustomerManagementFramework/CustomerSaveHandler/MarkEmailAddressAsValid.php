<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFramework\CustomerSaveHandler;

use CustomerManagementFramework\DataValidator\EmailValidator;
use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

/**
 * normalizes the zip field of a given customer according to several country zip formats
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class MarkEmailAddressAsValid implements CustomerSaveHandlerInterface
{
    private $config;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    private $markValidField;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;

        $this->logger = $logger;

        $this->markValidField = $this->config->markValidField ? : 'emailOk';
    }


    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function process(CustomerInterface $customer)
    {
        $setter = 'set' . ucfirst($this->markValidField);

        $validator = new EmailValidator();

        if($validator->isValid($customer->getEmail())) {
            $customer->$setter(true);
        } else {
            $customer->$setter(false);
        }
    }
}