<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use Fixpunkt\FpSocial\Domain\Interfaces\RecordInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class PostLink extends AbstractEntity implements RecordInterface
{
    /** @var Post|null  */
    protected ?Post $post = null;
    /** @var Account|null  */
    protected Account|null $account = null;
    /** @var bool */
    protected $hidden = false;

    public function getPost()
    {
        return $this -> post;
    }
    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this -> post = $post;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this -> account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account): void
    {
        $this -> account = $account;
    }

    /**
     * @return bool
     */
    public function getHidden(): bool
    {
        return $this -> hidden;
    }
    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this -> hidden = $hidden;
    }

    // Interface Methods

    public function getId(): string
    {
        return $this -> post -> getId();
    }
    public function getNetwork(): string
    {
        /** @var Account $account */
        $account = $this -> post -> getAccounts() -> current();
        return $account -> getNetwork();
    }
    public function getUrl(): string
    {
        return $this -> post -> getUrl();
    }
    public function getUpdatedTime()
    {
        return $this -> post -> getUpdatedTime();
    }
    public function getMessage(): string
    {
        return $this -> post -> getMessage();
    }
    public function getPicture(): string
    {
        return $this -> post -> getPicture();
    }
    public function getLink(): string
    {
        return $this -> post -> getLink();
    }
    public function getHeadline(): string
    {
        return $this -> post -> getHeadline();
    }
    public function getPictures()
    {
        return $this -> post -> getPictures();
    }
    public function asJson(): array
    {
        return $this -> post -> asJson();
    }
    public function getSelectedOrFirstPicture()
    {
        return $this -> post -> getSelectedOrFirstPicture();
    }
    public function getIdentifier(): string
    {
        return 'tx_fpsocial_domain_model_postlink:' . $this -> getUid();
    }
}
