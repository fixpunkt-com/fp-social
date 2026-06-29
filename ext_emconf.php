<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'File Protector',
    'description' => 'Restricts access to file storages based on frontend login, user groups, or backend session.',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'fixpunkt für digitales GmbH',
    'author_email' => 'office@fixpunkt.com',
    'author_company' => 'fixpunkt für digitales GmbH',
    'version' => '3.1.2',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-13.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];