<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

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
     *
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
