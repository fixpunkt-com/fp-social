<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$ll = 'LLL:EXT:fp_social/Resources/Private/Language/locallang_flex_output.xlf:';

// Own group for the CType selector and the content element wizard
ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'CType',
    'fpsocial',
    $ll . 'plugin.group',
    'after:plugins'
);

// Register Plugins
$wallPluginSignature = ExtensionUtility::registerPlugin(
    'FpSocial',
    'Wall',
    $ll . 'plugin.wall.title',
    'fpsocial-plugin-wall',
    'fpsocial',
    $ll . 'plugin.wall.description'
);
$postPluginSignature = ExtensionUtility::registerPlugin(
    'FpSocial',
    'Post',
    $ll . 'plugin.post.title',
    'fpsocial-plugin-post',
    'fpsocial',
    $ll . 'plugin.post.description'
);

// Register new fields
$columns = [
    'tx_fpsocial_accounts' => [
        'exclude' => 0,
        'label' => $ll . 'mainsheet.accounts',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'itemsProcFunc' => \Fixpunkt\FpSocial\Events\RecordCollectionEvent::class . '->getCollectionsForTca',
            'allowNonIdValues' => true,
        ],
    ],
    'tx_fpsocial_records' => [
        'exclude' => 0,
        'label' => $ll . 'mainsheet.records',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'itemsProcFunc' => \Fixpunkt\FpSocial\Events\RecordCollectionEvent::class . '->getAllRecordsForTca',
            'allowNonIdValues' => true,
        ],
    ],
    'tx_fpsocial_hashtags' => [
        'exclude' => 0,
        'label' => $ll . 'mainsheet.hashtags',
        'config' => [
            'type' => 'group',
            'allowed' => 'tx_fpsocial_domain_model_hashtag',
        ],
    ],
    'tx_fpsocial_post' => [
        'exclude' => 0,
        'label' => $ll . 'mainsheet.post',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'itemsProcFunc' => \Fixpunkt\FpSocial\Events\RecordCollectionEvent::class . '->getAllRecordsForTca',
            'allowNonIdValues' => true,
            'minitems' => 1,
            'maxitems' => 1,
        ],
    ],
    'tx_fpsocial_post_crop' => [
        'exclude' => 0,
        'label' => $ll . 'sheetPost.crop',
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
            'default' => 1,
        ],
    ],
    'tx_fpsocial_post_characters' => [
        'exclude' => 0,
        'label' => $ll . 'sheetPost.characters',
        'displayCond' => 'FIELD:tx_fpsocial_post_crop:=:1',
        'config' => [
            'default' => 150,
            'type' => 'number',
            'required' => true,
        ],
    ],
    'tx_fpsocial_post_compact' => [
        'exclude' => 0,
        'label' => $ll . 'sheetPost.compact',
        'config' => [
            'type' => 'check',
        ],
    ],
    'tx_fpsocial_post_picture' => [
        'exclude' => 0,
        'label' => $ll . 'sheetPost.picture',
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
            'default' => 1,
        ],
    ],
    'tx_fpsocial_post_picture_crop' => [
        'exclude' => 0,
        'label' => $ll . 'sheetPost.pictureCrop',
        'displayCond' => 'FIELD:tx_fpsocial_post_picture:=:1',
        'config' => [
            'type' => 'check',
        ],
    ],
    'tx_fpsocial_wall_columns' => [
        'exclude' => 0,
        'label' => $ll . 'sheetWall.columns',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => 1, 'value' => 1],
                ['label' => 2, 'value' => 2],
                ['label' => 3, 'value' => 3],
                ['label' => 4, 'value' => 4],
                ['label' => 5, 'value' => 5],
                ['label' => 6, 'value' => 6],
            ],
        ],
    ],
    'tx_fpsocial_wall_rows' => [
        'exclude' => 0,
        'label' => $ll . 'sheetWall.rows',
        'config' => [
            'type' => 'number',
            'required' => true,
        ],
    ],
    'tx_fpsocial_wall_loadnewer_enable' => [
        'exclude' => 0,
        'label' => $ll . 'sheetWall.loadNewer.enable',
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
        ],
    ],
    'tx_fpsocial_wall_replace' => [
        'exclude' => 0,
        'label' => $ll . 'sheetWall.replace.description',
        'displayCond' => 'FIELD:tx_fpsocial_wall_loadnewer_enable:=:1',
        'config' => [
            'type' => 'check',
        ],
    ],
    'tx_fpsocial_wall_loadbefore_enable' => [
        'exclude' => 0,
        'label' => $ll . 'sheetWall.loadBefore.enable',
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
        ],
    ],
    'tx_fpsocial_wall_loadbefore_label' => [
        'exclude' => 0,
        'label' => $ll . 'sheetWall.loadBefore.label',
        'displayCond' => 'FIELD:tx_fpsocial_wall_loadbefore_enable:=:1',
        'config' => [
            'type' => 'input',
            'required' => true,
        ],
    ],
];
ExtensionManagementUtility::addTCAcolumns('tt_content', $columns);

$GLOBALS['TCA']['tt_content']['palettes']['tx_fpsocial_post'] = [
    'label' => $ll . 'sheetPost',
    'showitem' => 'tx_fpsocial_post_crop,tx_fpsocial_post_characters,--linebreak--,tx_fpsocial_post_compact,--linebreak--,tx_fpsocial_post_picture,tx_fpsocial_post_picture_crop',
];
$GLOBALS['TCA']['tt_content']['palettes']['tx_fpsocial_wall'] = [
    'label' => $ll . 'sheetWall',
    'showitem' => 'tx_fpsocial_wall_columns,tx_fpsocial_wall_rows,--linebreak--,tx_fpsocial_wall_loadnewer_enable,tx_fpsocial_wall_replace,--linebreak--,tx_fpsocial_wall_loadbefore_enable,tx_fpsocial_wall_loadbefore_label',
];

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;' . $ll . 'plugin.settings,tx_fpsocial_accounts,tx_fpsocial_records,tx_fpsocial_hashtags,--palette--;;tx_fpsocial_post,--palette--;;tx_fpsocial_wall',
    $wallPluginSignature,
    'before:--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;' . $ll . 'plugin.settings,tx_fpsocial_post,--palette--;;tx_fpsocial_post',
    $postPluginSignature,
    'before:--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance'
);
