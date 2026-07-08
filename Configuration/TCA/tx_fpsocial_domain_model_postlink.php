<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_postlink',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],        'iconfile' => 'EXT:fp_social/Resources/Public/Icons/Models/tx_fpsocial_domain_model_postlink.svg',
    ],
    'types' => [
        '1' => ['showitem' => '
		    account,post,hidden'],
    ],
    'columns' => [
        'account' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_postlink.account',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_fpsocial_domain_model_account',
                'foreign_table' => 'tx_fpsocial_domain_model_account',
                'maxitems' => 1,
                'minitems' => 1,
                'size' => 1,
            ],
        ],
        'post' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_postlink.post',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_fpsocial_domain_model_post',
                'foreign_table' => 'tx_fpsocial_domain_model_post',
                'maxitems' => 1,
                'minitems' => 1,
                'size' => 1,
            ],
        ],
        'hidden' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_postlink.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_postlink.hidden.option',
                    ],
                ],
            ],
        ],
    ],
];
