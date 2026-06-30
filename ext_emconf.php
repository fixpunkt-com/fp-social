<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Social Wall',
    'description' => 'Social Wall',
    'category' => 'plugin',
    'author' => 'Yannik Börgener',
    'author_company' => 'fixpunkt für digitales GmbH',
    'state' => 'stable',
    'version' => '17.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'php' => '8.1.0-8.4.99',
            'fp_social_bridge' => '1.2.0-0.0.0',
            'fp_base_utilities' => '2.3.0-0.0.0',
            'list_type_migration' => '1.0.0-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
