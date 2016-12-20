<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:46
 */

namespace CustomerManagementFramework\Testing\DataTransformer;

class Zip2StateTest extends \PHPUnit_Framework_TestCase
{

    public function testCorrectTransformationsAt()
    {
        $transfomer = new \CustomerManagementFramework\DataTransformer\Zip2State\At;

        $tests = [
            '1020' => 'Wien',
            '5202' =>  'Salzburg',
            '5020' =>  'Salzburg',
            '7421' =>  'Steiermark',
            '9323' =>  'Steiermark',
            '2475' =>  'Burgenland',
        ];

        foreach($tests as $from => $to) {

            $result = $transfomer->transform($from);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsDe()
    {
        $transfomer = new \CustomerManagementFramework\DataTransformer\Zip2State\De;

        $tests = [
            '80331' => 'Bayern',
            '9553' => null,
        ];

        foreach($tests as $from => $to) {

            $result = $transfomer->transform($from);

            $this->assertEquals($to, $result);
        }
    }

    public function testCorrectTransformationsCh()
    {
        $transfomer = new \CustomerManagementFramework\DataTransformer\Zip2State\Ch;

        $tests = [
            '8614' => 'Zürich, Thurgau',
            '9553' => 'Ostschweiz',
        ];

        foreach($tests as $from => $to) {

            $result = $transfomer->transform($from);

            $this->assertEquals($to, $result);
        }
    }

}