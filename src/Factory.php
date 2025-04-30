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
