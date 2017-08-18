<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Service;

use Pimcore\Model\Object\Concrete;

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

    public function toArray(Concrete $object)
    {
        $fieldDefintions = $object->getClass()->getFieldDefinitions();

        $result = [];

        $result['id'] = $object->getId();

        foreach ($fieldDefintions as $fd) {
            $fieldName = $fd->getName();
            $result[$fieldName] = $fd->getForWebserviceExport($object);
        }

        $result['modificationDate'] = $object->getModificationDate();
        $result['creationDate'] = $object->getCreationDate();

        return $result;
    }
}
