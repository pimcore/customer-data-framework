<?php

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
