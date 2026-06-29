<?php

declare(strict_types=1);

use Fixpunkt\FpSocial\Controller\FrontendController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Plugin konfigurieren
ExtensionUtility::configurePlugin(
    'FpSocial',
    'Post',
    [
        FrontendController::class => 'single',
    ],
    // non-cacheable actions
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
ExtensionUtility::configurePlugin(
    'FpSocial',
    'Wall',
    [
        FrontendController::class => 'wall',
    ],
    // non-cacheable actions
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
ExtensionUtility::configurePlugin(
    'FpSocial',
    'Ajax',
    [
        FrontendController::class => 'ajaxLoadOlder, ajaxLoadNewer',
    ],
    // non-cacheable actions
    [
        FrontendController::class => 'ajaxLoadOlder, ajaxLoadNewer',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

// Show error in Backend if no site selected
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'fp_social',
    'setup',
    "@import 'EXT:fp_social/Configuration/TypoScript/notloaded/setup.typoscript'"
);
