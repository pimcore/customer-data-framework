<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Testing\DataTransformer;

class ZipTest extends \PHPUnit_Framework_TestCase
{
    public function testCorrectTransformationsDe()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\De();

        $tests = [
            '6125' => '06125',
            'D-6125' =>  '06125',
            'D-06125' =>  '06125',
            '24113 Molf' =>  '24113',
            'Molf' => 'Molf',
            '123456' => '123456',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsNl()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\Nl();

        $tests = [
            '1234ta' => '1234 TA',
            '1234ta City' => '1234 TA',
            '1234' => '1234',
            '1234t' => '1234T',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsAt()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\At();

        $tests = [
            '5733 Bramberg' => '5733',
            'A-5733' => '5733',
            '57333' => '57333',
            '5733 Mühlbach 1' => '5733',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsDk()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\Dk();

        $tests = [
            '1234 Test' => '1234',
            'DK-1234' => '1234',
            '12345' => '12345',
            '1234 Test 1' => '1234',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsBe()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\Be();

        $tests = [
            '1234 Test' => '1234',
            'DK-1234' => '1234',
            '12345' => '12345',
            '1234 Test 1' => '1234',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsCh()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\Ch();

        $tests = [
            '1234 Test' => '1234',
            'CH-1234' => '1234',
            '12345' => '12345',
            '1234 Test 1' => '1234',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsRu()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\Ru();

        $tests = [
            '123456 Test' => '123456',
            'RU-123456' => '123456',
            '1234567' => '1234567',
            '123456 Test 1' => '123456',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsSe()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\Se();

        $tests = [
            '12345' => '123 45',
            '123456' => '123456',
            'SE 12345' => '123 45',
            'SE-123 45' => '123 45',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsGb()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip\Gb();

        $tests = [
            'RM11AA' => 'RM1 1AA',
            'rm11AA' => 'RM1 1AA',
            'rm11AA london' => 'RM1 1AA',
            'london rm11AA' => 'RM1 1AA',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }
}
