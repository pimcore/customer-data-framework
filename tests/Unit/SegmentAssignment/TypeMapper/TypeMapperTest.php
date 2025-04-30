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

namespace CustomerManagementFrameworkBundle\Tests\Unit\SegmentAssignment\TypeMapper;

use Codeception\Test\Unit;
use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapper;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

class TypeMapperTest extends Unit
{
    /**
     * @var TypeMapper
     */
    private $sut = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = new TypeMapper();
    }

    public function testGetTypeStringByObject()
    {
        $objects = [new Document(), new Asset(), new DataObject()];
        $expected = ['document', 'asset', 'object'];
        $actual = array_map(function ($item) {
            return $this->sut->getTypeStringByObject($item);
        }, $objects);

        self::assertSame($expected, $actual);
    }
}
