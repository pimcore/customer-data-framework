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

namespace CustomerManagementFrameworkBundle\DataTransformer\Zip2State;

class Ch extends AbstractTransformer
{
    protected $zipRegions = [
        'Westschweiz (Süd)' => [[1000, 1999]],
        'Westschweiz (Nord)' => [[2000, 2999]],
        'Bern/Oberwallis' => [[3000, 3999]],
        'Basel' => [[4000, 4999]],
        'Aargau' => [[5000, 5999]],
        'Zentralschweiz, Tessin' => [[6000, 6999]],
        'Graubünden' => [[7000, 7999]],
        'Zürich, Thurgau' => [[8000, 8999]],
        'Ostschweiz' => [[9000, 9999]],
    ];
}
