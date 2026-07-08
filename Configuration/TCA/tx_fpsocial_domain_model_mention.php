<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_mention',
        'label' => 'display_name',
        'sortby' => 'display_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'iconfile' => 'EXT:fp_social/Resources/Public/Icons/Models/tx_fpsocial_domain_model_mention.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'display_name,system_name,posts'],
    ],
    'columns' => [
        'posts' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_mention.post',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_fpsocial_domain_model_post',
                'MM' => 'tx_fpsocial_post_mention_mm',
            ],
        ],
        'display_name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_mention.display_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'system_name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_mention.system_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
    ],
];
