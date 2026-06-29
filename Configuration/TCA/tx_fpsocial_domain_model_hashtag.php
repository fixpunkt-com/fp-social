<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_hashtag',
        'label' => 'hashtag',
        'sortby' => 'hashtag',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'searchFields' => 'hashtag',
        'iconfile' => 'EXT:fp_social/Resources/Public/Icons/Models/tx_fpsocial_domain_model_hashtag.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'hashtag,posts'],
    ],
    'columns' => [
        'posts' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_hashtag.post',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_fpsocial_domain_model_post',
                'MM' => 'tx_fpsocial_post_hashtag_mm',
            ],
        ],
        'hashtag' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_hashtag.hashtag',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
    ],
];
