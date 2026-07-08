<?php

declare(strict_types=1);

use Fixpunkt\FpSocial\Domain\Model\Account;

$tca = [
    'ctrl' => [
        'title'	=> 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account',
        'label' => 'network',
        'label_userFunc' => \Fixpunkt\FpSocial\Userfuncs\Labels::class . '->account',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'default_sortby' => 'network',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:fp_social/Resources/Public/Icons/Models/tx_fpsocial_domain_model_account.svg',
        'type' => 'network',
    ],
    'palettes' => [
        'network' => [
            'showitem' => 'access, network',
        ],
        'facebook' => [
            'showitem' => 'label, channel',
        ],
        'linkedin' => [
            'showitem' => 'li_mode, label, channel',
        ],
        'bluesky' => [
            'showitem' => 'channel',
        ],
        'instagram' => [
            'showitem' => 'in_mode, label, channel, in_hashtag, in_hashtag_mode',
        ],
        'wordpress' => [
            'showitem' => 'wp_url, wp_mode, wp_tag, wp_author',
        ],
        'youtube' => [
            'showitem' => 'channel',
        ],
        'synchronization' => [
            'showitem' => 'synchronize, approve, synchronization_interval, --linebreak--, last_synchronization, last_successful_synchronization, --linebreak--, synchronization_error',
        ],
    ],
    'types' => [
        Account\Facebook::class => [
            'showitem' => '
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.network;network,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.settings;facebook,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.synchronization;synchronization,
		    posts
		    ',
            'label' => Account\Facebook::DESCRIPTION,
        ],
        Account\Instagram::class => [
            'showitem' => '
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.network;network,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.settings;instagram,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.synchronization;synchronization,
		    posts
		    ',
            'label' => Account\Instagram::DESCRIPTION,
        ],
        Account\Wordpress::class => [
            'showitem' => '
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.network;network,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.settings;wordpress,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.synchronization;synchronization,
		    posts
		    ',
            'label' => Account\Wordpress::DESCRIPTION,
        ],
        Account\Youtube::class => [
            'showitem' => '
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.network;network,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.settings;youtube,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.synchronization;synchronization,
		    posts
		    ',
            'label' => Account\Youtube::DESCRIPTION,
        ],
        Account\LinkedIn::class => [
            'showitem' => '
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.network;network,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.settings;linkedin,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.synchronization;synchronization,
		    posts
		    ',
            'label' => Account\LinkedIn::DESCRIPTION,
        ],
        Account\Bluesky::class => [
            'showitem' => '
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.network;network,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.settings;bluesky,
		    --palette--;LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.palettes.synchronization;synchronization,
		    posts
		    ',
            'label' => Account\Bluesky::DESCRIPTION,
        ],
    ],
    'columns' => [
        'network' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.network',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'Facebook',
                        'value' => Account\Facebook::class,
                    ],
                    [
                        'label' => 'Instagram',
                        'value' => Account\Instagram::class,
                    ],
                    [
                        'label' => 'Wordpress',
                        'value' => Account\Wordpress::class,
                    ],
                    [
                        'label' => 'Youtube',
                        'value' => Account\Youtube::class,
                    ],
                    [
                        'label' => 'LinkedIn',
                        'value' => Account\LinkedIn::class,
                    ],
                    [
                        'label' => 'Bluesky',
                        'value' => Account\Bluesky::class,
                    ],
                ],
            ],
        ],
        'access' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.access',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_fpsocial_domain_model_access',
            ],
        ],
        'label' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.label',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'channel' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.channel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'posts' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.posts',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_fpsocial_domain_model_postlink',
                'foreign_field' => 'account',
            ],
        ],
        'wp_url' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.wp_url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'wp_mode' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.wp_mode',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.wp_mode.posts',
                        'value' => 'posts',
                    ],
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.wp_mode.tag',
                        'value' => 'tag',
                    ],
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.wp_mode.author',
                        'value' => 'author',
                    ],
                ],
            ],
        ],
        'wp_tag' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.wp_tag',
            'displayCond' => 'FIELD:wp_mode:=:tag',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'wp_author' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.wp_author',
            'displayCond' => 'FIELD:wp_mode:=:author',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'tw_mode' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.tw_mode',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.tw_mode.user_profile',
                        'value' => 'user_timeline',
                    ],
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.tw_mode.user_profile_posts',
                        'value' => 'user_timeline_posts',
                    ],
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.tw_mode.hashtag',
                        'value' => 'hashtag',
                    ],
                ],
            ],
        ],
        'tw_hashtag' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.tw_hashtag',
            'displayCond' => 'FIELD:tw_mode:=:hashtag',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'in_mode' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.in_mode',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.in_mode.profile',
                        'value' => 'profile',
                    ],
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.in_mode.hashtag',
                        'value' => 'hashtag',
                    ],
                ],
            ],
        ],
        'in_hashtag' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.in_hashtag',
            'displayCond' => 'FIELD:in_mode:=:hashtag',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'in_hashtag_mode' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.in_hashtag_mode',
            'displayCond' => 'FIELD:in_mode:=:hashtag',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.in_hashtag_mode.recent_media',
                        'value' => 'recent_media',
                    ],
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.in_hashtag_mode.top_media',
                        'value' => 'top_media',
                    ],
                ],
            ],
        ],
        'li_mode' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.li_mode',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.li_mode.shares',
                        'value' => 'shares',
                    ],
                    [
                        'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.li_mode.ugc_posts',
                        'value' => 'ugc_posts',
                    ],
                ],
            ],
        ],
        'synchronize' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.synchronize',
            'config' => [
                'type' => 'check',
            ],
        ],
        'approve' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.approve',
            'config' => [
                'default' => true,
                'type' => 'check',
            ],
        ],
        'last_synchronization' => [
            'exclude' => true,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.last_synchronization',
            'config' => [
                'type' => 'datetime',
                'dbType' => 'datetime',
                'nullable' => true,
            ],
        ],
        'last_successful_synchronization' => [
            'exclude' => true,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.last_successful_synchronization',
            'config' => [
                'type' => 'datetime',
                'dbType' => 'datetime',
                'nullable' => true,
            ],
        ],
        'synchronization_interval' => [
            'exclude' => true,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.synchronization_interval',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'Immer',
                        'value' => '',
                    ],
                    [
                        'label' => 'Alle 15 Minuten',
                        'value' => 'PT15M',
                    ],
                    [
                        'label' => 'Alle 30 Minuten',
                        'value' => 'PT30M',
                    ],
                    [
                        'label' => 'Alle 60 Minuten',
                        'value' => 'PT1H',
                    ],
                    [
                        'label' => 'Alle 6 Stunden',
                        'value' => 'PT6H',
                    ],
                    [
                        'label' => 'Alle 12 Stunden',
                        'value' => 'PT12H',
                    ],
                    [
                        'label' => 'Alle 24 Stunden',
                        'value' => 'P1D',
                    ],
                ],
            ],
        ],
        'synchronization_error' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:tx_fpsocial_domain_model_account.synchronization_error',
            'config' => [
                'type' => 'text',
            ],
        ],

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
    ],
];
$tca['types'][0] = &$tca['types'][Account\Facebook::class];
return $tca;
