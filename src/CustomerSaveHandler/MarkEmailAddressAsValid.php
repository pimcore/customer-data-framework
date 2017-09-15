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

/**
 * marks an email address as valid if it has a valid format
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class MarkEmailAddressAsValid extends AbstractCustomerSaveHandler
{
    /**
     * @var string
     */
    private $markValidField;

    public function __construct($markValidField = 'emailOk')
    {
        $this->markValidField = $markValidField;
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
