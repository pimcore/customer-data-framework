<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Helper;

use Pimcore\Bundle\NumberSequenceGeneratorBundle\Generator;

class SequenceNumber
{
    const TABLE_NAME = 'plugin_cmf_sequence_numbers';

    /**
     * @return Generator
     */
    protected static function getGenerator()
    {
        return \Pimcore::getContainer()->get(Generator::class);
    }

    /**
     * Get current number of sequence.
     *
     * @param string $sequenceName
     *
     * @return int
     */
    public static function getCurrent($sequenceName)
    {
        return self::getGenerator()->getCurrent($sequenceName);
    }

    /**
     * Sets current number of sequence to $sequenceValue
     *
     * @param string $sequenceName
     * @param int $sequenceValue
     *
     * @return int
     */
    public static function setCurrent($sequenceName, $sequenceValue = 10000)
    {
        return self::getGenerator()->setCurrent($sequenceName, $sequenceValue);
    }

    /**
     * Incremets sequence number by 1 and returns the new generated number. If the sequence didn't exist before, the sequence will be set to $startingNumber.
     *
     * @param string $sequenceName
     * @param int $startingNumber
     *
     * @return int
     */
    public static function getNext($sequenceName, $startingNumber = 10000)
    {
        return self::getGenerator()->getNext($sequenceName, $startingNumber);
    }
}
