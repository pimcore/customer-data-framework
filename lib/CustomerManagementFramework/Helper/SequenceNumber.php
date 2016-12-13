<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 13.12.2016
 * Time: 14:57
 */

namespace CustomerManagementFramework\Helper;


use Pimcore\Logger;

class SequenceNumber
{
    public static function getNext($sequenceName, $startingNumber = 10000) {
        $sequenceFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/cmf-sequence-number-" . $sequenceName . ".pid";
        $handle = self::SemaphoreWait();

        if(file_exists($sequenceFile)) {
            $number = file_get_contents($sequenceFile);
        } else {
            $number = $startingNumber;
        }

        $number += 1;

        file_put_contents($sequenceFile, $number);

        self::SemaphoreSignal($handle);

        Logger::info("Generated Sequenence Number " . $sequenceName . " " . $number . " (pid : " . getmypid() . ")");


        return $number;
    }

    protected static function getLockFilename() {
        $lockFilename = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/cmf-sequence-number.pid";
        return $lockFilename;
    }

    private static function SemaphoreWait() {
        $filename = self::getLockFilename();

        $handle = fopen($filename, 'w') or die("Error opening file.");
        if (flock($handle, LOCK_EX)) {
            //nothing...
        } else {
            die("Could not lock file.");
        }
        return $handle;
    }

    private static function SemaphoreSignal($handle) {
        fclose($handle);
    }
}