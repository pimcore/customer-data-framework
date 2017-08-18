<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveHandler;

use CustomerManagementFrameworkBundle\DataValidator\EmailValidator;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
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

        $this->markValidField = $this->config->markValidField ?: 'emailOk';
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
    {
        $setter = 'set'.ucfirst($this->markValidField);

        $validator = new EmailValidator();

        if ($validator->isValid($customer->getEmail())) {
            $customer->$setter(true);
        } else {
            $customer->$setter(false);
        }
    }
}
