<?php

declare(strict_types=1);

defined('TYPO3') or die();

// Define fields.
$tempColumns = [
    'protected' => [
        'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:sys_file_storage.protected',
        'exclude' => 1,
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ]
    ],
    'protected_by_default' => [
        'label' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:sys_file_storage.protected_by_default',
        'exclude' => 1,
        'displayCond' => 'FIELD:protected:REQ:true',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ]
    ]
];

// Add fields to the general record description without rendering them in the backend yet.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_storage', $tempColumns);

// Add fields to a new palette.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_file_storage',
    'protection',
    'protected,protected_by_default'
);

// Add the new palette after the title to render it in the backend.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_storage',
    '--palette--;LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang.xlf:sys_file_storage.palette.protection;protection',
    '',
    'after:is_online'
);
