<?php

namespace CustomerManagementFrameworkBundle\ExportToolkit\Getter;

use Carbon\Carbon;
use ExportToolkit\ExportService\IGetter;
use Pimcore\Model\Element\ElementInterface;

/**
 * Loads a date from one or multiple fields, trying to parse date if field does not return a DateTime
 */
class Date implements IGetter
{
    /**
     * @param ElementInterface $object
     * @param null $config
     * @return \DateTime|null
     */
    public static function get($object, $config = null)
    {
        $config = (array)$config;
        $fields = isset($config['fields']) ? $config['fields'] : [];

        foreach ($fields as $field) {
            $getter = 'get'.ucfirst($field);
            $value = $object->$getter();

            if ($value) {
                if (!($value instanceof \DateTime)) {
                    $value = Carbon::parse($value);
                }

                return $value;
            }
        }
    }
}
