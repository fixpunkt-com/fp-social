<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Social Wall',
    'description' => 'Social Wall',
    'category' => 'plugin',
    'author' => 'Yannik Börgener',
    'author_company' => 'fixpunkt für digitales GmbH',
    'state' => 'stable',
    'version' => '17.5.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.99.99',
            'php' => '8.4.0-8.99.99',
            'fp_social_bridge' => '1.3.0-1.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
