<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

return [
    'debug_mode' => true,
    'debug_file' => PIMCORE_LOG_DIRECTORY . '/hybridauth.log',
    'providers'  => [
        'Google' => [
            'enabled' => true,
            'keys'    => [
                'id'     => 'GOOGLE_ID',
                'secret' => 'GOOGLE_SECRET'
            ],
            'scope' => implode(' ', [
                'openid',
                'email',
                'profile'
            ])
        ],

        'Twitter' => [
            'enabled' => true,
            'keys' => [
                'key'    => 'TWITTER_KEY',
                'secret' => 'TWITTER_SECRET'
            ],
        ]
    ]
];
