<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ExportToolkit\Getter\MailChimp;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use ExportToolkit\ExportService\IGetter;

class Address implements IGetter
{
    /**
     * @param CustomerInterface $object
     * @param null $config
     *
     * @return array
     */
    public static function get($object, $config = null)
    {
        $result = [
            'addr1' => $object->getStreet() ?: '',
            'addr2' => '',
            'city' => $object->getCity() ?: '',
            'state' => '',
            'zip' => $object->getZip() ?: '',
            'country' => $object->getCountryCode() ?: '',
        ];

        foreach ($result as $key => $value) {
            $result[$key] = trim($value);
        }

        return $result;
    }
}
