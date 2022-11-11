<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-19
 * Time: 10:42 AM
 */

namespace CustomerManagementFrameworkBundle\Tests\Support\Unit\SegmentAssignment\TypeMapper;

use Codeception\Test\Unit;
use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapper;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

class TypeMapperTest extends Unit {

    /**
     * @var TypeMapper
     */
    private $sut = null;

    protected function setUp(): void {
        parent::setUp();
        $this->sut = new TypeMapper();
    }

    public function testGetTypeStringByObject() {
        $objects = [new Document(), new Asset(), new DataObject()];
        $expected = ['document', 'asset', 'object'];
        $actual = array_map(function($item) {
            return $this->sut->getTypeStringByObject($item);
            }, $objects);

        self::assertSame($expected, $actual);
    }
}
