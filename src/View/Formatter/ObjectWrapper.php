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

namespace CustomerManagementFrameworkBundle\View\Formatter;

class ObjectWrapper
{
    /**
     * @var mixed
     */
    protected $object;

    /**
     * @param mixed $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * @return mixed|string
     */
    public function __toString()
    {
        if (!is_object($this->object)) {
            return $this->object ?? '';
        }

        if (method_exists($this->object, '__toString')) {
            return call_user_func([$this->object, '__toString']);
        }

        return get_class($this->object);
    }
}
