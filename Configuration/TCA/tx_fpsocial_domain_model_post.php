<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post',
        'label' => 'id',
        'label_userFunc' => \Fixpunkt\FpSocial\Userfuncs\Labels::class . '->post',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'searchFields' => 'id,url,updated_time,headline,message',
        'iconfile' => 'EXT:fp_social/Resources/Public/Icons/Models/tx_fpsocial_domain_model_post.svg',
    ],
    'palettes' => [
        'post_data' => [
            'showitem' => 'accounts, id',
        ],
        'more' => [
            'showitem' => 'link, url',
        ],
        'pictures' => [
            'showitem' => 'pictures,selected_picture',
        ],
        'visible' => [
            'showitem' => 'origin_deleted',
        ],
    ],
    'types' => [
        '1' => ['showitem' => '
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.palettes.post_data;post_data,
		    updated_time, headline, message, 
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.palettes.more;more,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.palettes.pictures;pictures,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.palettes.visible;visible,
		    hashtags,mentions,
		    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'columns' => [
        'accounts' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.accounts',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_fpsocial_domain_model_postlink',
                'foreign_field' => 'post',
            ],
        ],
        'id' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'requires' => true,
                'eval' => 'trim',
            ],
        ],
        'url' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
        'updated_time' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.updated_time',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
                'dbType' => 'datetime',
            ],
        ],
        'headline' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.headline',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'message' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.message',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'reqired' => true,
                'eval' => 'trim',
            ],
        ],
        'pictures' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.pictures',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_fpsocial_domain_model_picture',
                'foreign_field' => 'post',
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                ],
            ],
        ],
        'selected_picture' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.selected_picture',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_fpsocial_domain_model_picture',
                'foreign_table_where' => 'AND tx_fpsocial_domain_model_picture.post=###THIS_UID###',
                'items' => [
                    [
                        'label' => 'erstes Bild nutzen',
                        'value' => 0,
                    ],
                ],
            ],
        ],
        'link' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.link',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'origin_deleted' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.origin_deleted',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.origin_deleted.option',
                    ],
                ],
            ],
        ],
        'mentions' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.mentions',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_fpsocial_domain_model_mention',
                'MM' => 'tx_fpsocial_post_mention_mm',
                'MM_opposite_field' => 'posts',
            ],
        ],
        'hashtags' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_post.hashtags',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_fpsocial_domain_model_hashtag',
                'MM' => 'tx_fpsocial_post_hashtag_mm',
                'MM_opposite_field' => 'posts',
            ],
        ],
    ],
];
