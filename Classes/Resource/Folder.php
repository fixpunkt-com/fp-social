<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Resource;

use Fixpunkt\FpFileprotector\Domain\Model\Protection;
use Fixpunkt\FpFileprotector\Domain\Repository\ProtectionRepository;
use TYPO3\CMS\Core\Resource as Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is not defined in TCA and only simplifies working with storages.
 */
class Folder extends Core\Folder
{
    /** @var ResourceStorage */
    protected $storage;

    /**
     * Returns protection for this folder or one of its parent folders.
     *
     * @param bool $recursive
     * @return Protection|null
     */
    public function getProtection(bool $recursive = true): ?Protection
    {
        /** @var ProtectionRepository $protectionRepository */
        $protectionRepository = GeneralUtility::makeInstance(ProtectionRepository::class);
        return $protectionRepository->getProtection($this, $recursive);
    }
    /**
     * Returns protection assigned directly to this folder.
     *
     * @return Protection|null
     */
    public function getOwnProtection(): ?Protection
    {
        return $this->getProtection(false);
    }

    /**
     * Checks whether this folder is the root level folder.
     *
     * @return bool
     */
    public function isRootLevelFolder(): bool
    {
        return $this->getStorage()->getRootLevelFolder()->getIdentifier() === $this->getIdentifier();
    }

    /**
     * @return bool
     */
    public function hasParentFolder(): bool
    {
        return !$this->isRootLevelFolder();
    }

    /**
     * Returns the protection status if this folder
     *
     * @return string
     */
    public function getProtectionStatus(): string
    {
        $protection = $this->getProtection();
        if ($protection && $protection->isProtected()) {
            // protection is set
            return $this->getOwnProtection() ? 'protected' : 'protected_by_parent';
        } else {
            // no protection is set
            return $this->storage->isProtectedByDefault() ? 'no_access' : 'public';
        }
    }

    /**
     * Returns whether access protection exists.
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return in_array($this->getProtectionStatus(), ['protected', 'protected_by_parent']);
    }

    /**
     * Returns whether this folder can be accessed.
     *
     * @return bool
     */
    public function isAccessible(): bool
    {
        return $this->getProtectionStatus() !== 'no_access';
    }

    /**
     * Returns whether this folder can be accessed.
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->getProtectionStatus() === 'public';
    }

    /**
     * Returns a speaking name if the Folder is the root folder
     *
     * @return string
     */
    public function getSpeakingName(): string
    {
        return $this->hasParentFolder() ? $this->getName() : $this->getStorage()->getName();
    }
}
