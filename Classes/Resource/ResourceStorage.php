<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Resource;

use Fixpunkt\FpFileprotector\Utility\HtaccessUtility;
use TYPO3\CMS\Core\Resource as Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is not defined in TCA and only simplifies working with storages.
 */
class ResourceStorage extends Core\ResourceStorage
{
    /**
     * @return bool
     */
    public function isProtected(): bool
    {
        return (bool)$this->storageRecord['protected'];
    }
    /**
     * @param bool $protected
     */
    public function setProtected(bool $protected): void
    {
        $this->storageRecord['protected'] = $protected;
    }

    /**
     * @return bool
     */
    public function isProtectedByDefault(): bool
    {
        return (bool)$this->storageRecord['protected_by_default'];
    }
    /**
     * @param bool $protectedByDefault
     */
    public function setProtectedByDefault(bool $protectedByDefault): void
    {
        $this->storageRecord['protected_by_default'] = $protectedByDefault;
    }

    /**
     * @return bool
     */
    public function hasHtaccess(): bool
    {
        /** @var HtaccessUtility $htaccessUtility */
        $htaccessUtility = GeneralUtility::makeInstance(HtaccessUtility::class, $this);
        return $htaccessUtility->hasHtaccess();
    }

    /**
     * Updates the .htaccess file.
     */
    public function modifyHtaccess(): void
    {
        /** @var HtaccessUtility $htaccessUtility */
        $htaccessUtility = GeneralUtility::makeInstance(HtaccessUtility::class, $this);

        if ($this->isProtected()) {
            $htaccessUtility->addHtaccess();
        } else {
            $htaccessUtility->removeHtaccess();
        }
    }
}
