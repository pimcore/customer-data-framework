<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-19
 * Time: 10:42 AM
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper;

use PHPUnit\Framework\TestCase;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\DataObject\AbstractObject;

class TypeMapperTest extends TestCase {

    /**
     * @var TypeMapper
     */
    private $sut = null;

    protected function setUp() {
        parent::setUp();
        $this->sut = new TypeMapper();
    }

    public function testGetTypeStringByObject() {
        $objects = [new Document(), new Asset(), new AbstractObject()];
        $expected = ['document', 'asset', 'object'];
        $actual = array_map(function($item) {
            return $this->sut->getTypeStringByObject($item);
            }, $objects);

        self::assertSame($expected, $actual);
    }
}
