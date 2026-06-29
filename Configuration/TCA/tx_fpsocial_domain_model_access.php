<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_access',
        'label' => 'fp_username',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'iconfile' => 'EXT:fp_social/Resources/Public/Icons/Models/tx_fpsocial_domain_model_access.svg',
    ],
    'types' => [
        0 => [
            'showitem' => 'fp_username,fp_access_token',
        ],
    ],
    'columns' => [
        'fp_username' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_access.fp_username',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'fp_access_token' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_access.fp_access_token',
            'config' => [
                'type' => 'password',
                'hashed' => false,
                'required' => true,
            ],
        ],
    ],
];
