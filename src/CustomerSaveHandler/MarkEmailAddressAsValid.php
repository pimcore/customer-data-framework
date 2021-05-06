<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
