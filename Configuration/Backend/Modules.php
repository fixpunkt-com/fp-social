<?php

declare(strict_types=1);

use Fixpunkt\FpSocial\Controller\AccountController;
use Fixpunkt\FpSocial\Controller\PostController;
use Fixpunkt\FpSocial\Controller\PostLinkController;
use Fixpunkt\FpSocial\Controller\SynchronizationController;

if (!defined('TYPO3')) {
    die('Access denied.');
}
return [
    'web_fpsocialmanage' => [
        'parent' => 'web',
        'access' => 'user',
        'path' => '/module/fpsocial/manage',
        'iconIdentifier' => 'fpsocial-bemodules-manage',
        'labels' => 'LLL:EXT:fp_social/Resources/Private/Language/locallang_module_manage.xlf',
        'extensionName' => 'FpSocial',
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'controllerActions' => [
            AccountController::class => 'list, show, update',
            SynchronizationController::class => 'list, update, account, post',
            PostController::class => 'search, update',
            PostLinkController::class => 'update',
        ],
    ],
];
