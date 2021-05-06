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

class At extends AbstractTransformer
{
    protected $zipRegions = [
        'Wien' => [[1000, 1901]],
        'Niederösterreich' => [
            [2000, 2413],
            [2431, 2472],
            [2481, 2490],
            [2492, 2881],
            [3001, 3333],
            [3340, 3973],
            [4300, 4303],
            [4392],
            [4431, 4441],
            [4441],
            [4482],
        ],
        'Burgenland' => [
            [2421, 2425],
            [2473, 2475],
            [2491],
            [7000, 7413],
            [7422, 7573],
            [8380, 8385],
        ],
        'Oberösterreich' => [
            [3334, 3335],
            [4000, 4294],
            [4310, 4391],
            [4400, 4421],
            [4442, 4481],
            [4483, 4985],
            [5120, 5145],
            [5211, 5283],
            [5310, 5311],
            [5360],
        ],
        'Salzburg' => [
            [5000, 5114],
            [5151, 5205],
            [5300, 5303],
            [5321, 5351],
            [5400, 5771],
        ],
        'Tirol' => [
            [6000, 6691],
            [9782],
            [9900, 9992],
        ],
        'Vorarlberg' => [[6700, 6993]],
        'Steiermark' => [
            [7421],
            [8000, 8363],
            [8401, 8993],
            [9323],
        ],
        'Kärnten' => [
            [9000, 9322],
            [9324, 9781],
            [9800, 9873],
        ],
    ];
}
