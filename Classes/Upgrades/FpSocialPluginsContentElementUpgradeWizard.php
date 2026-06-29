<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Upgrades;

// composer req linawolf/list-type-migration
use Linawolf\ListTypeMigration\Upgrades\AbstractListTypeToCTypeUpdate;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;

#[UpgradeWizard('FpSocialPluginsContentElementUpgradeWizard')]
final class FpSocialPluginsContentElementUpgradeWizard extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'fpsocial_post' => 'fpsocial_post',
            'fpsocial_wall' => 'fpsocial_wall',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrates all list_type plugins to content elements (CType) for FpSocial extension';
    }

    public function getDescription(): string
    {
        return 'Migrates all list_type plugins to content elements (CType) for FpSocial extension';
    }
}
