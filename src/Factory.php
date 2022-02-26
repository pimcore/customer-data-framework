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

namespace CustomerManagementFrameworkBundle;

class Factory
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

    /**
     * @param string $className
     * @param string|null $needsToBeSubclassOf
     * @param array $constructorParams
     *
     * @return object
     *
     * @throws \Exception
     */
    public function createObject($className, $needsToBeSubclassOf = null, array $constructorParams = [])
    {
        if (!class_exists($className)) {
            throw new \Exception(sprintf('class %s does not exist', $className));
        }

        $object = new $className(...array_values($constructorParams));

        if (!is_null($needsToBeSubclassOf) && !is_subclass_of($object, $needsToBeSubclassOf)) {
            throw new \Exception(sprintf('%s needs to extend/implement %s', $className, $needsToBeSubclassOf));
        }

        return $object;
    }
}
