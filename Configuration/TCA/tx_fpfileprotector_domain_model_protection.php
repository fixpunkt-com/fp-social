<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection',
        'label' => 'folder',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'searchFields' => 'folder',
        'iconfile' => 'EXT:fp_fileprotector/Resources/Public/Icons/Models/tx_fpfileprotector_domain_model_protection.svg'
    ],
    'palettes' => [
        'folder' => [
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.palette.folder',
            'showitem' => 'storage,folder',
        ],
        'fe' => [
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.palette.fe',
            'showitem' => 'fe_login,--linebreak--,user_groups,--linebreak--,users',
        ],
        'be' => [
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.palette.be',
            'showitem' => 'be_login',
        ],
    ],
    'types' => [
        0 => ['showitem' => '--palette--;;folder,--palette--;;fe,--palette--;;be'],
    ],
    'columns' => [
        'storage' => [
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.storage',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0]
                ],
                'foreign_table' => 'sys_file_storage',
                'foreign_table_where' => 'AND {#sys_file_storage}.{#protected} = 1',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
            ]
        ],
        'folder' => [
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.folder',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [],
                'itemsProcFunc' => 'TYPO3\\CMS\\Core\\Resource\\Service\\UserFileMountService->renderTceformsSelectDropdown',
                'default' => '',
            ]
        ],
        'fe_login' => [
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.fe_login',
            'exclude' => 1,
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ]
        ],
        'be_login' => [
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.be_login',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ]
        ],
        'user_groups' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.user_groups',
            'displayCond' => 'FIELD:fe_login:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'MM' => 'tx_fpfileprotector_protection_fegroups_mm'
            ],
        ],
        'users' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:tx_fpfileprotector_domain_model_protection.users',
            'displayCond' => 'FIELD:fe_login:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'MM' => 'tx_fpfileprotector_protection_feusers_mm'
            ],
        ],
    ],
];
