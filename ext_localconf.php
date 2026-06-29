<?php

declare(strict_types=1);

defined('TYPO3') || die('Access denied.');

// Extend the storage record.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\ResourceStorage::class] = [
    'className' => \Fixpunkt\FpFileprotector\Resource\ResourceStorage::class
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Folder::class] = [
    'className' => \Fixpunkt\FpFileprotector\Resource\Folder::class
];
