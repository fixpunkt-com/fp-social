<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Upgrades;

use Fixpunkt\FpBaseUtilities\Upgrades\FlexformToTcaMigrationWizard;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;

#[UpgradeWizard('FlexformToTcaPostMigrationWizard')]
class FlexformToTcaPostMigrationWizard extends FlexformToTcaMigrationWizard
{
    protected string $cType = 'fpsocial_post';

    protected array $mapping = [
        'settings.post' => 'tx_fpsocial_post',
        'settings.view.post.crop' => 'tx_fpsocial_post_crop',
        'settings.view.post.characters' => 'tx_fpsocial_post_characters',
        'settings.view.post.compact' => 'tx_fpsocial_post_compact',
        'settings.view.post.picture' => 'tx_fpsocial_post_picture',
        'settings.view.post.pictureCrop' => 'tx_fpsocial_post_picture_crop',
    ];

    public function getTitle(): string
    {
        return 'Flexform to TCA FpSocial Post Migration Wizard';
    }

    public function getDescription(): string
    {
        return 'Migrates Flexform settings of the Post Plugin of FpSocial to TCA fields.';
    }
}
