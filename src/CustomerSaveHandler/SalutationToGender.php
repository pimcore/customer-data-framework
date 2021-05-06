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

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

/**
 * Maps a salutation field to a gender field. This can automatically adjust the gender based on the salutation.
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class SalutationToGender extends AbstractCustomerSaveHandler
{
    /**
     * @var string
     */
    private $salutationField;

    /**
     * @var array
     */
    private $genderMap;

    public function __construct($salutationField = 'salutation', $genderMap = ['mr' => 'male', 'mrs' => 'female'])
    {
        $this->salutationField = $salutationField;
        $this->genderMap = $genderMap;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
    {
        $getter = 'get'.ucfirst($this->salutationField);
        $salutation = $customer->$getter();

        if (isset($this->genderMap[$salutation])) {
            $customer->setGender($this->genderMap[$salutation]);
        }
    }
}
