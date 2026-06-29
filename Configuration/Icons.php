<?php

declare(strict_types=1);

return [
    'fpsocial-bemodules-main' => [
        // Icon provider class
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:fp_social/Resources/Public/Icons/Modules/main.svg',
    ],
    'fpsocial-bemodules-manage' => [
        // Icon provider class
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:fp_social/Resources/Public/Icons/Modules/manage.jpg',
    ],
    'fpsocial-bemodules-maintenance' => [
        // Icon provider class
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:fp_social/Resources/Public/Icons/Modules/maintenance.jpg',
    ],
];
