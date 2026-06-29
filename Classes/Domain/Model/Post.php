<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use Fixpunkt\FpSocial\Domain\Repository\PostLinkRepository;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Post extends AbstractEntity
{
    /** @var \Fixpunkt\FpSocial\Domain\Model\Account */
    protected ?Account $account = null;
    /** @var string */
    protected string $id = '';
    /** @var string */
    protected string $network = '';
    /** @var string */
    protected string $url = '';
    /** @var \DateTime */
    protected ?\DateTime $updatedTime = null;
    /** @var string */
    protected string $message = '';
    /** @var string */
    protected string $picture = '';
    /** @var string */
    protected string $link = '';
    /** @var string */
    protected string $headline = '';
    /** @var bool */
    protected bool $originDeleted = false;
    /**
     * @var ObjectStorage<Picture>
     */
    #[Cascade(['value' => 'remove'])]
    protected ObjectStorage|null $pictures = null;
    /**
     * @var ObjectStorage<PostLink>
     */
    #[Lazy()]
    #[Cascade(['value' => 'remove'])]
    protected ObjectStorage|null $accounts = null;
    /** @var Picture */
    protected ?Picture $selectedPicture = null;
    /** @var ObjectStorage<Hashtag> */
    #[Lazy()]
    protected ?ObjectStorage $hashtags = null;
    /** @var ObjectStorage<Mention> */
    #[Lazy()]
    protected ?ObjectStorage $mentions = null;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        $this -> pictures = new ObjectStorage();
        $this -> accounts = new ObjectStorage();
        $this -> hashtags = new ObjectStorage();
        $this -> mentions = new ObjectStorage();
    }

    /**
     * @return Account
     */
    public function getAccount()
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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    /**
     * @param string $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * Gibt den Partial Folder des Netzwerks zurück.
     * @return string
     */
    public function getPartialFolder(): string
    {
        $networkClass = $this -> getNetworkClass();
        return $networkClass::getPartialFolder();
    }
    /**
     * Gibt die Account-Klasse zurück, mit dem dieser Post verbunden ist.
     * @return string
     */
    public function getNetworkClass(): string
    {
        $postLink = $this -> getAccounts() -> toArray()[0] ?? null;
        return $postLink ? get_class($postLink -> getAccount()) : '';
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedTime()
    {
        return $this->updatedTime;
    }
    /**
     * @param \DateTime $updatedTime
     */
    public function setUpdatedTime(\DateTime $updatedTime): void
    {
        $this->updatedTime = $updatedTime;
    }

    /**
    * @return string $message
    */
    public function getMessage(): string
    {
        return $this->message;
    }
    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this -> message = $message;
    }

    /**
     * @return string $picture
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     */
    public function setPicture(string $picture): void
    {
        $this->picture = $picture;
    }

    /**
     * @return string $link
     */
    public function getLink(): string
    {
        return $this->link;
    }
    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getHeadline(): string
    {
        return $this -> headline;
    }
    /**
     * @param string $headline
     */
    public function setHeadline(string $headline): void
    {
        $this -> headline = $headline;
    }

    /**
     * @param bool $originDeleted
     */
    public function setOriginDeleted(bool $originDeleted): void
    {
        if ($originDeleted) {
            /** @var PostLink $account */
            foreach ($this -> getAccounts() as $account) {
                $account -> setHidden(true);
            }
        }
        $this->originDeleted = $originDeleted;
    }
    /**
     * @return bool
     */
    public function getOriginDeleted(): bool
    {
        return $this->originDeleted;
    }

    /**
     * @param Picture $picture
     */
    public function addPictures(Picture $picture)
    {
        if (!$this -> hasPicture($picture)) {
            $this->pictures->attach($picture);
        }
    }
    /**
     * @param Picture $picture
     */
    public function removePictures(Picture $picture)
    {
        $this->pictures->detach($picture);
    }
    /**
     * @return ObjectStorage
     */
    public function getPictures()
    {
        return $this -> pictures;
    }
    /**
     * @param ObjectStorage $pictures
     */
    public function setPictures(ObjectStorage $pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * Prüft ob ein Bild bereits in Pictures gespeichert ist.
     * @param Picture $picture
     * @return bool
     */
    public function hasPicture(Picture $picture): bool
    {
        /** @var Picture $existingPicture */
        foreach ($this -> getPictures() -> toArray() as $existingPicture) {
            if ($picture -> getUriIdentifier() == $existingPicture -> getUriIdentifier()) {
                return true;
            }
        }
        return false;
    }

    public function getPicturesByUri(string $uri): ?Picture
    {
        $uriIdentifier = $this -> getNetworkClass()::getPictureIdentifier($uri);
        /** @var Picture $picture */
        foreach ($this -> getPictures() as $picture) {
            if ($picture -> getUriIdentifier() == $uriIdentifier) {
                return $picture;
            }
        }
        return null;
    }

    /**
     * @param PostLink $account
     */
    public function addAccounts(PostLink $account)
    {
        $this->accounts->attach($account);
    }
    /**
     * @param PostLink $account
     */
    public function removeAccounts(PostLink $account)
    {
        $this->accounts->detach($account);
    }
    /**
     * @return ObjectStorage
     */
    public function getAccounts(): ?ObjectStorage
    {
        if (Environment::isCli() || ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            /** @var PostLinkRepository $postLinkRepository */
            $postLinkRepository = GeneralUtility::makeInstance(PostLinkRepository::class);
            $postLinkRepository -> setDefaultQuerySettings(
                $postLinkRepository -> createQuery() -> getQuerySettings() -> setIgnoreEnableFields(true)
            );
            $links = $postLinkRepository -> findBy(['post' => $this]);

            if ($links -> count()) {
                $objectStorage = new ObjectStorage();
                /** @var PostLink $link */
                foreach ($links as $link) {
                    $objectStorage -> attach($link);
                }
                return $objectStorage;
            }
            return $this -> accounts;

        }
        return $this -> accounts;

    }
    /**
     * @param ObjectStorage $accounts
     */
    public function setAccounts(ObjectStorage $accounts)
    {
        $this->accounts = $accounts;
    }
    /**
     * Prüft ob der Post bereits mit dem Account verbunden ist (unabhängig davon, ob diese Verbindung sichtbar ist oder nicht).
     * @param Account $account
     * @return bool
     */
    public function hasAccount(Account $account): bool
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_postlink');
        $queryBuilder -> getRestrictions() -> removeByType(HiddenRestriction::class);
        $statement = $queryBuilder
            -> select('uid')
            -> from('tx_fpsocial_domain_model_postlink')
            -> where(
                $queryBuilder->expr()->eq('post', $queryBuilder->createNamedParameter($this -> getUid(), Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('account', $queryBuilder->createNamedParameter($account -> getUid(), Connection::PARAM_INT)),
            )
            -> executeQuery();
        return count($statement -> fetchAllAssociative()) > 0;
    }

    public function asJson(): array
    {
        return [
            'message' => $this -> getMessage(),
            'post_url' => $this -> getLink(),
            'update_time' => $this -> getUpdatedTime(),
            'link' => $this -> link,
            'picture' => $this -> picture,
        ];
    }

    /**
     * @return Picture
     */
    public function getSelectedPicture()
    {
        return $this -> selectedPicture;
    }
    /**
     * @param Picture $selectedPicture
     */
    public function setSelectedPicture(Picture $selectedPicture)
    {
        $this -> selectedPicture = $selectedPicture;
    }
    public function getSelectedOrFirstPicture(): ?Picture
    {
        if ($this -> selectedPicture) {
            return $this -> selectedPicture;
        }
        if (count($this -> getPictures())) {
            return $this -> getPictures() -> toArray()[0];
        }
        return null;
    }

    /**
     * @return ObjectStorage|null
     */
    public function getMentions(): ?ObjectStorage
    {
        return $this->mentions;
    }
    /**
     * @param ObjectStorage $mentions
     */
    public function setMentions(?ObjectStorage $mentions): void
    {
        $this->mentions = $mentions;
    }
    /**
     * @param Mention $mention
     */
    public function addMention(Mention $mention): void
    {
        $this -> mentions -> attach($mention);
    }
    /**
     * @param Mention $mention
     */
    public function removeMention(Mention $mention): void
    {
        $this -> mentions -> detach($mention);
    }

    /**
     * @return ObjectStorage|null
     */
    public function getHashtags(): ?ObjectStorage
    {
        return $this->hashtags;
    }
    /**
     * @param ObjectStorage $hashtags
     */
    public function setHashtags(ObjectStorage $hashtags): void
    {
        $this->hashtags = $hashtags;
    }
    /**
     * @param Hashtag $hashtag
     */
    public function addHashtag(Hashtag $hashtag): void
    {
        $this -> hashtags -> attach($hashtag);
    }
    /**
     * @param Hashtag $hashtag
     */
    public function removeHashtag(Hashtag $hashtag): void
    {
        $this -> hashtags -> detach($hashtag);
    }
}
