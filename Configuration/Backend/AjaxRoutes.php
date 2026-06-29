<?php

declare(strict_types=1);

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'fpsocial_synchronize_deep' => [
        'path' => '/web/FpSocialManage/synchronize',
        'target' => \Fixpunkt\FpSocial\Controller\AjaxController::class . '::synchronizeAction',
    ],
];
