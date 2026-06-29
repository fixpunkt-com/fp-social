<?php

declare(strict_types=1);
return [
    // Subklassen registrieren
    \Fixpunkt\FpSocial\Domain\Model\Account::class => [
        'subclasses' => [
            \Fixpunkt\FpSocial\Domain\Model\Account\Facebook::class => \Fixpunkt\FpSocial\Domain\Model\Account\Facebook::class,
            \Fixpunkt\FpSocial\Domain\Model\Account\Instagram::class => \Fixpunkt\FpSocial\Domain\Model\Account\Instagram::class,
            \Fixpunkt\FpSocial\Domain\Model\Account\LinkedIn::class => \Fixpunkt\FpSocial\Domain\Model\Account\LinkedIn::class,
            \Fixpunkt\FpSocial\Domain\Model\Account\Wordpress::class => \Fixpunkt\FpSocial\Domain\Model\Account\Wordpress::class,
            \Fixpunkt\FpSocial\Domain\Model\Account\Youtube::class => \Fixpunkt\FpSocial\Domain\Model\Account\Youtube::class,
            \Fixpunkt\FpSocial\Domain\Model\Account\Bluesky::class => \Fixpunkt\FpSocial\Domain\Model\Account\Bluesky::class,
        ],
    ],

    // Subklassen mit Tabelle verbinden
    \Fixpunkt\FpSocial\Domain\Model\Account\Facebook::class => [
        'tableName' => 'tx_fpsocial_domain_model_account',
        'recordType' => \Fixpunkt\FpSocial\Domain\Model\Account\Facebook::class,
    ],
    \Fixpunkt\FpSocial\Domain\Model\Account\Instagram::class => [
        'tableName' => 'tx_fpsocial_domain_model_account',
        'recordType' => \Fixpunkt\FpSocial\Domain\Model\Account\Instagram::class,
    ],
    \Fixpunkt\FpSocial\Domain\Model\Account\LinkedIn::class => [
        'tableName' => 'tx_fpsocial_domain_model_account',
        'recordType' => \Fixpunkt\FpSocial\Domain\Model\Account\LinkedIn::class,
    ],
    \Fixpunkt\FpSocial\Domain\Model\Account\Wordpress::class => [
        'tableName' => 'tx_fpsocial_domain_model_account',
        'recordType' => \Fixpunkt\FpSocial\Domain\Model\Account\Wordpress::class,
    ],
    \Fixpunkt\FpSocial\Domain\Model\Account\Youtube::class => [
        'tableName' => 'tx_fpsocial_domain_model_account',
        'recordType' => \Fixpunkt\FpSocial\Domain\Model\Account\Youtube::class,
    ],
    \Fixpunkt\FpSocial\Domain\Model\Account\Bluesky::class => [
        'tableName' => 'tx_fpsocial_domain_model_account',
        'recordType' => \Fixpunkt\FpSocial\Domain\Model\Account\Bluesky::class,
    ],
];
