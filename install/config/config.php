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
    'General' => [
        'CustomerPimcoreClass' => 'Customer',
        'mailBlackListFile' => PIMCORE_WEBSITE_PATH . '/config/plugins/CustomerManagementFramework/mail-blacklist.txt'
    ],

    'Encryption' => [
        // echo \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
        // do not check this in on real projects and keep it secret
        'secret' => 'def00000a2fe8752646f7d244c950f0399180a7ab1fb38e43edaf05e0ff40cfa2bbedebf726268d0fc73d5f74d6992a886f83eb294535eb0683bb15db9c4929bbd138aee',
    ],

    'SegmentManager' => [
        'segmentBuilders' => [
            /*[
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
            ]*/
        ],
        'segmentsFolder' => [
            'manual' => '/segments/manual',
            'calculated' => '/segments/__calculated',
        ]
    ],


    'CustomerSaveValidator' => [
        'requiredFields' => [
            /*['email'],
            ['firstname', 'name', 'zip', 'birthday'],*/
        ],
        'checkForDuplicates' => true
    ],

    'CustomerDuplicatesService' => [
        'duplicateCheckFields' => [
            /*['firstname', 'lastname', 'zip', 'birthday'],*/
        ],

        'DuplicatesIndex' => [
            /*'enableDuplicatesIndex' => true,
            'duplicateCheckFields' => [

                [
                    'firstname' => ['soundex' => true, 'metaphone' => true, 'similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText::class],
                    'zip' => ['similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\Zip::class],
                    'street' => ['soundex' => true, 'metaphone' => true, 'similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText::class],
                    'birthDate' => ['similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\BirthDate::class],

                ],
                [
                    'lastname' => ['soundex' => true, 'metaphone' => true, 'similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText::class],
                    'firstname' => ['soundex' => true, 'metaphone' => true, 'similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText::class],
                    'zip' => ['similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\Zip::class],
                    'city' => ['soundex' => true, 'metaphone' => true, 'similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText::class],
                    'street' => ['soundex' => true, 'metaphone' => true, 'similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText::class]
                ],
                [
                    'email' => ['metaphone' => true, 'similarity' => \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText::class, 'similarityTreshold' => 90]
                ]
            ],
            'dataTransformers' => [
                'street' => \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Street::class,
                'firstname' => \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify::class,
                'city' => \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify::class,
                'lastname' => \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify::class,
                'birthDate' => \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Date::class,
            ],*/
        ],

        'DuplicatesView' => [
            'listFields' => [
                /*"id",
                "email",
                [
                    "firstname",
                    "lastname"
                ],
                "street",
                [
                    "zip",
                    "city"
                ],
                "birthDate",
                "shoeSize",*/
            ],
        ]

    ],

    'CustomerMerger' => [
        'archiveDir' => '/customers/__archive'
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
            'exporters' => [
                'csv' => [
                    'name'       => 'CSV',
                    'icon'       => 'fa fa-file-text-o',
                    'exporter'   => \CustomerManagementFrameworkBundle\CustomerList\Exporter\Csv::class,
                    'properties' => [
                        'id',
                        'active',
                        'gender',
                        'email',
                        'phone',
                        'firstname',
                        'lastname',
                        'street',
                        'zip',
                        'city',
                        'countryCode',
                        'idEncoded',
                    ],
                    'exportSegmentsAsColumns' => true
                ],

                'xlsx' => [
                    'name'       => 'XLSX',
                    'icon'       => 'fa fa-file-excel-o',
                    'exporter'   => \CustomerManagementFrameworkBundle\CustomerList\Exporter\Xlsx::class,
                    'properties' => [
                        'id',
                        'active',
                        'gender',
                        'email',
                        'phone',
                        'firstname',
                        'lastname',
                        'street',
                        'zip',
                        'city',
                        'countryCode',
                        'idEncoded',
                    ],
                    'exportSegmentsAsColumns' => true
                ],
            ],
        ]
    ],

    /*'MailChimp' => [
        'apiKey' => 'c64d7cc4fe11e068c515389cfe3a8607-us14',
        'listId' => '7a54a0555c',
    ]*/
];
