<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\ActionTrigger;

class RuleEnvironment implements RuleEnvironmentInterface
{
    /**
     * @var array
     */
    private $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function has($name): bool
    {
        return $this->offsetExists($name);
    }

    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function get($name, $default = null)
    {
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }

        return $default;
    }

    /**
     * @return \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()/* : \ArrayIterator */
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)/* : mixed */
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)/* : void */
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)/* : void */
    {
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
        }
    }

    /**
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function serialize()/* : string */
    {
        return serialize($this->data);
    }

    /**
     * @param string $serialized
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function unserialize($serialized)/* : void */
    {
        $this->data = unserialize($serialized);
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()/* : array */
    {
        return $this->data;
    }
}
