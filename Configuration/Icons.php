<?php

declare(strict_types=1);

$isVersion14OrHigher = (new \TYPO3\CMS\Core\Information\Typo3Version()) -> getMajorVersion() >= 14;

return [
    'fpsocial-bemodules-manage' => $isVersion14OrHigher
        ? [
            // Icon provider class
            'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            'source' => 'EXT:fp_social/Resources/Public/Icons/Modules/manage.svg',
        ]
        : [
            // Icon provider class
            'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            'source' => 'EXT:fp_social/Resources/Public/Icons/Modules/manage.jpg',
        ],
    'fpsocial-plugin-wall' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:fp_social/Resources/Public/Icons/Extension.svg',
    ],
    'fpsocial-plugin-post' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:fp_social/Resources/Public/Icons/Models/tx_fpsocial_domain_model_post.svg',
    ],
];
