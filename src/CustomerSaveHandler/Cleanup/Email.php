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

namespace CustomerManagementFrameworkBundle\CustomerSaveHandler\Cleanup;

use CustomerManagementFrameworkBundle\CustomerSaveHandler\AbstractCustomerSaveHandler;
use CustomerManagementFrameworkBundle\DataTransformer\Cleanup\Email as EmailTransformer;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

/**
 * normalizes the zip field of a given customer according to several country zip formats
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class Email extends AbstractCustomerSaveHandler
{
    /**
     * @var string
     */
    private $emailField;

    public function __construct($emailField = 'email')
    {
        $this->emailField = $emailField;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
    {
        $getter = 'get' . ucfirst($this->emailField);
        $setter = 'set' . ucfirst($this->emailField);

        if ($email = $customer->$getter()) {
            $cleaner = new EmailTransformer();

            $customer->$setter($cleaner->transform($email));
        }
    }
}
