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
 * marks an email address as valid if it has a valid format
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class MarkEmailAddressAsValid extends AbstractCustomerSaveHandler
{


    private $markValidField;

    public function __construct($config, LoggerInterface $logger)
    {
        parent::__construct($config, $logger);

        $this->markValidField = $this->config->markValidField ? : 'emailOk';
    }


    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
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