<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\View\Formatter;

class ObjectWrapper
{
    /**
     * @var mixed
     */
    protected $object;

    /**
     * @param $object
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
            return $this->object;
        }

        if (method_exists($this->object, '__toString')) {
            return call_user_func([$this->object, '__toString']);
        }

        return get_class($this->object);
    }
}
