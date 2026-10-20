<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Social Wall',
    'description' => 'Social Wall',
    'category' => 'plugin',
    'author' => 'Yannik Börgener',
    'author_company' => 'fixpunkt für digitales GmbH',
    'state' => 'stable',
    'version' => '17.8.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.3.99',
            'php' => '8.2.1-8.4.99',
            'fp_social_bridge' => '1.5.0-1.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
