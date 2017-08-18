<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Testing\View\Formatter;

use CustomerManagementFrameworkBundle\Testing\Fixtures\View\Formatter\NoToStringObject;
use CustomerManagementFrameworkBundle\Testing\Fixtures\View\Formatter\ToStringObject;
use CustomerManagementFrameworkBundle\View\Formatter\ObjectWrapper;

class ObjectWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider scalarDataProvider
     */
    public function testScalarReturnsItsValue($value)
    {
        $wrapper = new ObjectWrapper($value);
        $this->assertEquals($value, $wrapper->__toString());
    }

    public function testToStringObjectReturnsItsValue()
    {
        $object  = new ToStringObject();
        $wrapper = new ObjectWrapper($object);

        $this->assertEquals(ToStringObject::TO_STRING_VALUE, $wrapper->__toString());
    }

    public function testNoToStringObjectReturnsClassName()
    {
        $object  = new NoToStringObject();
        $wrapper = new ObjectWrapper($object);

        $this->assertEquals(NoToStringObject::class, $wrapper->__toString());
    }

    public function scalarDataProvider()
    {
        return [
            'string'     => ['foo'],
            'int'        => [42],
            'float'      => [42.5],
            'null'       => [null],
            'bool:true'  => [true],
            'bool:false' => [false]
        ];
    }
}
