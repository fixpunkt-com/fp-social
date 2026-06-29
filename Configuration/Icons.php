<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'tx-fpfileprotector-module' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:fp_fileprotector/Resources/Public/Icons/Modules/protection.jpg',
    ],

    // Icons for Folder-Tree

    'tx-fpfileprotector-folder-protected' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:fp_fileprotector/Resources/Public/Icons/Locks/protected.svg',
    ],
    'tx-fpfileprotector-folder-public' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:fp_fileprotector/Resources/Public/Icons/Locks/public.svg',
    ],
    'tx-fpfileprotector-folder-no-access' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:fp_fileprotector/Resources/Public/Icons/Locks/no_access.svg',
    ],
];
