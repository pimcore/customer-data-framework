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

class Zip2StateTest extends \PHPUnit_Framework_TestCase
{
    public function testCorrectTransformationsAt()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip2State\At;

        $tests = [
            '1020' => 'Wien',
            '5202' =>  'Salzburg',
            '5020' =>  'Salzburg',
            '7421' =>  'Steiermark',
            '9323' =>  'Steiermark',
            '2475' =>  'Burgenland',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsDe()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip2State\De;

        $tests = [
            '80331' => 'Bayern',
            '9553' => null,
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsCh()
    {
        $transfomer = new \CustomerManagementFrameworkBundle\DataTransformer\Zip2State\Ch;

        $tests = [
            '8614' => 'Zürich, Thurgau',
            '9553' => 'Ostschweiz',
        ];

        foreach ($tests as $from => $to) {
            $result = $transfomer->transform($from, []);

            $this->assertEquals($to, $result);
        }
    }
}
