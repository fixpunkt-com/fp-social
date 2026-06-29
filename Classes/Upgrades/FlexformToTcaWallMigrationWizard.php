<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Upgrades;

use Fixpunkt\FpBaseUtilities\Upgrades\FlexformToTcaMigrationWizard;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;

#[UpgradeWizard('FlexformToTcaWallMigrationWizard')]
class FlexformToTcaWallMigrationWizard extends FlexformToTcaMigrationWizard
{
    protected string $cType = 'fpsocial_wall';

    protected array $mapping = [
        'settings.accounts' => 'tx_fpsocial_accounts',
        'settings.records' => 'tx_fpsocial_records',
        'settings.hashtags' => 'tx_fpsocial_hashtags',

        'settings.view.post.crop' => 'tx_fpsocial_post_crop',
        'settings.view.post.characters' => 'tx_fpsocial_post_characters',
        'settings.view.post.compact' => 'tx_fpsocial_post_compact',
        'settings.view.post.picture' => 'tx_fpsocial_post_picture',
        'settings.view.post.pictureCrop' => 'tx_fpsocial_post_picture_crop',

        'settings.wall.columns' => 'tx_fpsocial_wall_columns',
        'settings.wall.rows' => 'tx_fpsocial_wall_rows',
        'settings.wall.loadNewer.enable' => 'tx_fpsocial_wall_loadnewer_enable',
        'settings.wall.replace' => 'tx_fpsocial_wall_replace',
        'settings.wall.loadBefore.enable' => 'tx_fpsocial_wall_loadbefore_enable',
        'settings.wall.loadBefore.label' => 'tx_fpsocial_wall_loadbefore_label',
    ];

    public function getTitle(): string
    {
        return 'Flexform to TCA FpSocial Wall Migration Wizard';
    }

    public function getDescription(): string
    {
        return 'Migrates Flexform settings of the Wall Plugin of FpSocial to TCA fields.';
    }
}
