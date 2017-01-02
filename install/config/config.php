<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 24.10.2016
 * Time: 15:12
 */

return [
    'General' => [
        'CustomerPimcoreClass' => 'Customer'
    ],

    'Encryption' => [
        // echo \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
        // do not check this in on real projects and keep it secret
        'secret' => 'def00000a2fe8752646f7d244c950f0399180a7ab1fb38e43edaf05e0ff40cfa2bbedebf726268d0fc73d5f74d6992a886f83eb294535eb0683bb15db9c4929bbd138aee',
    ],

    'SegmentManager' => [
        'segmentBuilders' => [
            [
                'segmentBuilder' => '\CustomerManagementFramework\SegmentBuilder\GenderSegmentBuilder',
                'segmentGroup' => 'Geschlecht',
                'valueMapping' => [
                    'male' => \CustomerManagementFramework\SegmentBuilder\GenderSegmentBuilder::MALE,
                    'female' =>\CustomerManagementFramework\SegmentBuilder\GenderSegmentBuilder::FEMALE,
                ],
                'maleSegmentName' => 'maennlich',
                'femaleSegmentName' => 'weiblich',
                'notsetSegmentName' => 'nicht definiert',
            ],
            [
                'segmentBuilder' => '\CustomerManagementFramework\SegmentBuilder\StateSegmentBuilder',
                'segmentGroup' => 'Bundesland',
                'countryTransformers' => [
                    'A' => 'CustomerManagementFramework\DataTransformer\Zip2State\At',
                    'AT' => 'CustomerManagementFramework\DataTransformer\Zip2State\At',
                    'D' => 'CustomerManagementFramework\DataTransformer\Zip2State\De',
                    'DE' => 'CustomerManagementFramework\DataTransformer\Zip2State\De',
                    'CH' => 'CustomerManagementFramework\DataTransformer\Zip2State\Ch',
                ]
            ],
            [
                'segmentBuilder' => '\CustomerManagementFramework\SegmentBuilder\AgeSegmentBuilder',
                'segmentGroup' => 'Alter',
                'birthDayField' => 'birthDate'
            ],
            [
                'segmentBuilder' => '\Website\CustomerManagementFramework\SegmentBuilder\RegularClient'
            ],
            [
                'segmentBuilder' => '\Website\CustomerManagementFramework\SegmentBuilder\Season'
            ]
        ],
        'segmentsFolder' => [
            'manual' => '/segments/manual',
            'calculated' => '/segments/__calculated',
        ]
    ],

    'CustomerProvider' => [
        'parentPath' => '/customers'
    ],

    'CustomerSaveManager' => [
        'saveHandlers' => [
            [
                'saveHandler' => '\CustomerManagementFramework\CustomerSaveHandler\NormalizeZip',
                'countryTransformers' =>
                    [
                        'A' => 'CustomerManagementFramework\DataTransformer\Zip\At',
                        'AT' => 'CustomerManagementFramework\DataTransformer\Zip\At',
                        'D' => 'CustomerManagementFramework\DataTransformer\Zip\De',
                        'DE' => 'CustomerManagementFramework\DataTransformer\Zip\De',
                        'NL' => 'CustomerManagementFramework\DataTransformer\Zip\Nl',
                        'DK' => 'CustomerManagementFramework\DataTransformer\Zip\Dk',
                        'BE' => 'CustomerManagementFramework\DataTransformer\Zip\Be',
                        'RU' => 'CustomerManagementFramework\DataTransformer\Zip\Ru',
                        'CH' => 'CustomerManagementFramework\DataTransformer\Zip\Ch',
                        'SE' => 'CustomerManagementFramework\DataTransformer\Zip\Se',
                        'GB' => 'CustomerManagementFramework\DataTransformer\Zip\Gb',
                    ]
            ],
            [
                'saveHandler' => '\CustomerManagementFramework\CustomerSaveHandler\RemoveBlacklistedEmails',
                'blackListFile' => PIMCORE_WEBSITE_PATH . '/config/plugins/CustomerManagementFramework/mail-blacklist.txt'
            ],
            [
                'saveHandler' => '\CustomerManagementFramework\CustomerSaveHandler\MarkEmailAddressAsValid',
                'markValidField' => 'emailOk'
            ],
            [
                'saveHandler' => '\Website\CustomerManagementFramework\CustomerSaveHandler\CustomerId'
            ]
        ]
    ],

    'CustomerSaveValidator' => [
        'requiredFields' => [
            ['email'],
            ['firstname', 'name', 'zip', 'birthday'],
        ]
    ],

    'CustomerDuplicatesService' => [
        'duplicateCheckFields' => [
            ['firstname', 'lastname', 'zip', 'birthday'],
        ]
    ],

    'CustomerList' => [
        'filterProperties' => [
            'equals' => [
                'id'     => 'o_id',
                'active' => 'active',
            ],
            'search' => [
                'email' => 'email',
                'name'  => [
                    'name',
                    'firstname',
                    'lastname',
                    'userName'
                ],
                'search' => [
                    'o_id',
                    'idEncoded',
                    'name',
                    'firstname',
                    'lastname',
                    'userName',
                    'email'
                ]
            ]
        ],

        'exporters' => [
            'csv' => [
                'name'       => 'CSV',
                'icon'       => 'fa fa-file-text-o',
                'exporter'   => \CustomerManagementFramework\CustomerList\Exporter\Csv::class,
                'properties' => [
                    'id',
                    'email',
                    'name'
                ]
            ],
        ]
    ],
    'Events' => [
        'plugin.cmf.new-activity' => '\CustomerManagementFramework\ActionTrigger\Event\NewActivity',
        'plugin.cmf.execute-segment-builders' => '\CustomerManagementFramework\ActionTrigger\Event\ExecuteSegmentBuilders'
    ],

    'MailChimp' => [
        'apiKey' => 'c64d7cc4fe11e068c515389cfe3a8607-us14',
        'listId' => '7a54a0555c',
    ]
];
