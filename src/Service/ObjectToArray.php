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

namespace CustomerManagementFrameworkBundle\Service;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Normalizer\NormalizerInterface;

class ObjectToArray
{
    private function __construct()
    {
    }

    /**
     * @return static
     */
    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    protected function loadRawDataFromContainer($container, $fieldName)
    {
        $data = null;
        $getter = 'get' . ucfirst($fieldName);
        if (method_exists($container, $getter)) {
            $data = $container->$getter();
        }

        return $data;
    }

    public function toArray(Concrete $object)
    {
        $fieldDefintions = $object->getClass()->getFieldDefinitions();

        $result = [];

        $result['id'] = $object->getId();

        foreach ($fieldDefintions as $fd) {
            $fieldName = $fd->getName();
            $value = $this->loadRawDataFromContainer($object, $fieldName);
            if (!empty($value) && $fd instanceof NormalizerInterface) {
                $result[$fieldName] = $fd->normalize($value);
            }
        }

        $result['modificationDate'] = $object->getModificationDate();
        $result['creationDate'] = $object->getCreationDate();

        return $result;
    }
}
