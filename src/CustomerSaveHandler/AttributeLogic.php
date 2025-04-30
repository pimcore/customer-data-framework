<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveHandler;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;

/**
 * Can be used when multiple address tabs are used to setup a logic to overrite the base address tab by other tabs/fields when needed.
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class AttributeLogic extends AbstractCustomerSaveHandler
{
    use LoggerAware;

    /**
     * @var array
     */
    private $fieldMapping;

    /**
     * AttributeLogic constructor.
     *
     * @param array ...$fieldMapping
     *
     * Example field mapping in yml config:
     * arguments:
     *  - from: profileStreet
     *    to: street
     *    overwriteIfNotEmpty: true
     *  - from: profileZip
     *    to: zip
     *    overwriteIfNotEmpty: true
     *  - from: profileCity
     *    to: city
     *    overwriteIfNotEmpty: true
     *  - from: profileCountryCode
     *    to: countryCode
     *    overwriteIfNotEmpty: true
     */
    public function __construct(... $fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    public function isOriginalCustomerNeeded()
    {
        return true;
    }

    /**
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
    {
        foreach ($this->fieldMapping as $item) {
            if (empty($item['from']) || empty($item['to'])) {
                continue;
            }

            $overwriteIfNotEmpty = isset($item['overwriteIfNotEmpty']) ? (bool) $item['overwriteIfNotEmpty'] : false;

            if (!$this->checkIfOverwriteIsNeeded($customer, $item['from'], $item['to'], $overwriteIfNotEmpty)) {
                continue;
            }

            $this->overwriteFieldValue($customer, $item['from'], $item['to']);
        }
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool $overwriteIfNotEmpty
     *
     * @return bool
     */
    protected function checkIfOverwriteIsNeeded(CustomerInterface $customer, $from, $to, $overwriteIfNotEmpty = false)
    {
        $fromGetter = 'get' . ucfirst($from);
        $toGetter = 'get' . ucfirst($to);

        // if target field is not empty and overwriteIfNotEmpty is not set => no update needed
        if (!$overwriteIfNotEmpty && $customer->$toGetter()) {
            return false;
        }

        if ($originalCustomer = $this->getOriginalCustomer()) {

            // if from field did not change => no update needed
            if ($customer->$fromGetter() == $originalCustomer->$fromGetter()) {
                return false;
            }

            // if to field changed => no update needed
            if ($originalCustomer->$toGetter() != $customer->$toGetter()) {
                return false;
            }
        } elseif ($customer->$toGetter()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $from
     * @param string $to
     */
    protected function overwriteFieldValue(CustomerInterface $customer, $from, $to)
    {
        $fromGetter = 'get' . ucfirst($from);
        $setter = 'set' . ucfirst($to);

        $customer->$setter($customer->$fromGetter());

        $this->getLogger()->debug(
            sprintf(
                'Overwrite field "%s" with field value from "%s" for customer ID %d',
                $to,
                $from,
                $customer->getId()
            )
        );
    }
}
