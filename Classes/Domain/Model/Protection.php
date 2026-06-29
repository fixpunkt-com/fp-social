<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Domain\Model;

use Fixpunkt\FpFileprotector\Domain\Repository\FolderRepository;
use Fixpunkt\FpFileprotector\Resource\Folder;
use Fixpunkt\FpFileprotector\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Protection extends AbstractEntity
{
    protected int $storage = 0;
    protected string $folder = '';
    protected bool $feLogin = false;
    protected bool $beLogin = false;
    /** @var ObjectStorage<FrontendUserGroup>|null */
    protected ?ObjectStorage $userGroups = null;
    /** @var ObjectStorage<FrontendUser>|null */
    protected ?ObjectStorage $users = null;

    public function __construct()
    {
        $this->userGroups = new ObjectStorage();
        $this->users = new ObjectStorage();
    }

    /**
     * @return int
     */
    public function getStorage(): int
    {
        return $this->storage;
    }
    /**
     * @param int $storage
     */
    public function setStorage(int $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }
    /**
     * @param string $folder
     */
    public function setFolder(string $folder): void
    {
        $this->folder = $folder;
    }
    /**
     * Returns the folder as an object.
     *
     * @return Folder|null
     */
    public function getFolderObject(): ?Folder
    {
        /** @var FolderRepository $folderRepository */
        $folderRepository = GeneralUtility::makeInstance(FolderRepository::class);
        return $folderRepository->findOneByCombinedIdentifier($this->getStorage() . ':' . $this->getFolder());
    }

    /**
     * @return bool
     */
    public function isFeLogin(): bool
    {
        return $this->feLogin;
    }
    /**
     * @param bool $feLogin
     */
    public function setFeLogin(bool $feLogin): void
    {
        $this->feLogin = $feLogin;
    }

    /**
     * @return bool
     */
    public function isBeLogin(): bool
    {
        return $this->beLogin;
    }
    /**
     * @param bool $beLogin
     */
    public function setBeLogin(bool $beLogin): void
    {
        $this->beLogin = $beLogin;
    }

    /**
     * @return ObjectStorage<FrontendUserGroup>|null
     */
    public function getUserGroups(): ?ObjectStorage
    {
        return $this->userGroups;
    }
    public function getUserGroupsUids(): array
    {
        $uids = [];
        /** @var FrontendUserGroup $userGroup */
        foreach ($this->getUserGroups() as $userGroup) {
            $uids[] = $userGroup->getUid();
        }
        return $uids;
    }
    /**
     * @param ObjectStorage<FrontendUserGroup> $userGroups
     */
    public function setUserGroups(ObjectStorage $userGroups): void
    {
        $this->userGroups = $userGroups;
    }
    /**
     * @param FrontendUserGroup $userGroup
     */
    public function addUserGroup(FrontendUserGroup $userGroup): void
    {
        $this->userGroups->attach($userGroup);
    }
    /**
     * @param FrontendUserGroup $userGroup
     */
    public function removeUserGroup(FrontendUserGroup $userGroup): void
    {
        $this->userGroups->detach($userGroup);
    }

    /**
     * @return ObjectStorage<FrontendUser>|null
     */
    public function getUsers(): ?ObjectStorage
    {
        return $this->users;
    }
    public function getUsersUids(): array
    {
        $uids = [];
        /** @var FrontendUser $user */
        foreach ($this->getUsers() as $user) {
            $uids[] = $user->getUid();
        }
        return $uids;
    }
    /**
     * @param ObjectStorage<FrontendUser> $users
     */
    public function setUsers(ObjectStorage $users): void
    {
        $this->users = $users;
    }
    /**
     * @param FrontendUser $user
     */
    public function addUser(FrontendUser $user): void
    {
        $this->users->attach($user);
    }
    /**
     * @param FrontendUser $user
     */
    public function removeUser(FrontendUser $user): void
    {
        $this->users->detach($user);
    }

    /**
     * Checks whether the current user is allowed to access the resource.
     *
     * @return bool
     */
    public function isGranted(): bool
    {
        if ($this->isFeLogin()) {
            $frontendUserUtility = GeneralUtility::makeInstance(FrontendUserUtility::class);
            $feUser = $frontendUserUtility->getCurrentFrontendUser();
            if ($feUser && $feUser->isLoggedIn()) {
                if ($this->getUserGroups()->count() === 0 && $this->getUsers()->count() === 0) {
                    return true;
                }

                if (in_array($feUser->get('id'), $this->getUsersUids(), true)) {
                    return true;
                }

                foreach ($feUser->get('groupIds') as $userGroupId) {
                    if (in_array($userGroupId, $this->getUserGroupsUids(), true)) {
                        return true;
                    }
                }
            }
        }

        $context = GeneralUtility::makeInstance(Context::class);
        if (
            $this->isBeLogin()
            && (bool)$context->getPropertyFromAspect('backend.user', 'isLoggedIn')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns whether the folder protection applies any restrictions.
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return $this->isFeLogin() || $this->isBeLogin();
    }
}
