<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Helper;

use Pimcore\Db;

class SequenceNumber
{
    const TABLE_NAME = 'plugin_cmf_sequence_numbers';

    /**
     * Get current number of sequence.
     *
     * @param $sequenceName
     * @param int $startingNumber
     *
     * @return int
     */
    public static function getCurrent($sequenceName, $startingNumber = 10000)
    {
        $db = Db::get();
        $number = $db->fetchOne('select number from '.self::TABLE_NAME.' where name = ?', $sequenceName);

        return intval($number) ?: $startingNumber;
    }

    /**
     * Sets current number of sequence to $sequenceValue
     * Take care: this method should only be used to set values initially, or to
     * reset values, if specific sequence limits are not reached. This is not
     * transactional safe!!
     * @param $sequenceName
     * @param int $sequenceValue
     *
     * @return int
     */
    public static function setCurrent($sequenceName, $sequenceValue = 10000)
    {
        $db = Db::get();
        try {
            $sql = sprintf ("REPLACE INTO %s (name,number) VALUES (?,?)", self::$TABLE_NAME);
            $db->executeQuery($sql,[$register, $sequenceValue ]);
            
            $logger->info(
                sprintf(
                    "Updated Sequence Number '%s' from %d to %d (pid :%s)",
                    $sequenceName,
                    $current,
                    $sequenceValue,
                    getmypid()
                )
            );
            
            return $sequenceValue;
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            //already existing
            throw new \RuntimeException(
                    sprintf(
                        'Sequence value of %d already exists!'
                    )
                );
        }
    }

    /**
     * Incremets sequence number by 1 and returns the new generated number. If the sequence didn't exist before, the sequence will be set to $startingNumber.
     *
     * @param $sequenceName
     * @param int $startingNumber
     *
     * @return int
     */
    public static function getNext($sequenceName, $startingNumber = 10000)
    {
        //transactional save see https://dev.mysql.com/doc/refman/5.7/en/innodb-locking-reads.html
        //the select last_insert_id() does not access any table. merely retrieves identifier information
        $sql = sprintf ("UPDATE %s SET number = LAST_INSERT_ID(number + 1) WHERE name=?", self::TABLE_NAME);
        $db = Db::get();
        $db->executeQuery($sql, [$sequenceName]);
        $nextNumber = $db->fetchOne('select LAST_INSERT_ID()');
        if ($nextNumber == 0) {
            try {
                $sql = sprintf ("INSERT  INTO %s (name,number) VALUES (?,?)", self::TABLE_NAME);
                $db->executeQuery($sql,[$sequenceName, $startingNumber ]);
                return $startingNumber;
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                //already existing; try again (avoid transactional collision)
                return self::getNext($sequenceName, $startingNumber);
            }
        }
        return $nextNumber;
    }
}
