<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
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
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
        }
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
