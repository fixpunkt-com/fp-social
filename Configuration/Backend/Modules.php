<?php

declare(strict_types=1);

use Fixpunkt\FpFileprotector\Controller\FileStorageController;
use Fixpunkt\FpFileprotector\Controller\FolderController;
use Fixpunkt\FpFileprotector\Controller\ProtectionController;

return [
    'file_FpFileprotectorProtection' => [
        'parent' => 'file',
        'access' => 'user',
        'iconIdentifier' => 'tx-fpfileprotector-module',
        'labels' => 'LLL:EXT:fp_fileprotector/Resources/Private/Language/locallang_module_protection.xlf',
        'extensionName' => 'FpFileprotector',
        'controllerActions' => [
            FolderController::class => [
                'show'
            ],
            FileStorageController::class => [
                'htaccess',
                'edit',
                'update'
            ],
            ProtectionController::class => [
                'new',
                'create',
                'edit',
                'update',
                'delete'
            ],
        ],
    ],
];
