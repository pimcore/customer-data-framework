<?php

return [
    'debug_mode' => true,
    "debug_file" => PIMCORE_LOG_DIRECTORY . '/hybridauth.log',
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
