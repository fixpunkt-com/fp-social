<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Repository;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Model\Picture;
use Fixpunkt\FpSocial\Domain\Model\Post;
use Fixpunkt\FpSocial\Domain\Model\PostLink;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class PostRepository extends Repository
{
    /** @var MentionRepository */
    protected MentionRepository $mentionRepository;
    /** @var HashtagRepository */
    protected HashtagRepository $hashtagRepository;
    /** @var array  */
    private array $newlyCreatedPosts = [];

    /**
     * PostRepository constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this -> mentionRepository = GeneralUtility::makeInstance(MentionRepository::class);
        $this -> hashtagRepository = GeneralUtility::makeInstance(HashtagRepository::class);
    }

    // Order by BE sorting
    protected $defaultOrderings = [
        'updatedTime' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * Fügt einen neuen Post hinzu.
     */
    public function add($post): void
    {
        \assert($post instanceof Post);
        $post -> setPid($this -> getPidOfFirstLink($post));
        parent::add($post);
    }

    /**
     * Sucht einen Post oder legt einen an.
     * @param Account $account
     * @param string $postId
     * @return Post
     */
    public function findOrCreatePost(Account $account, string $postId): Post
    {
        // Prüfe ob der Post schon in der Datenbank vorhanden
        $post = $this -> findOneBy([
            'accounts.account.network' => $account -> getNetwork(),
            'id' => $postId,
        ]);

        // Prüfen ob der Post neu angelegt wurde
        if (!$post && array_key_exists($account -> getUid(), $this -> newlyCreatedPosts) && array_key_exists($postId, $this -> newlyCreatedPosts[$account -> getUid()])) {
            $post = $this -> newlyCreatedPosts[$account -> getUid()][$postId];
        }

        // Wenn es ihn nicht gibt, neu anlegen
        if (!$post) {
            /** @var Post $post */
            $post = GeneralUtility::makeInstance(Post::class);
            $post -> setId($postId);
            $post -> setPid($account -> getPid());
            $this -> newlyCreatedPosts[$account -> getUid()][$postId] = $post;
        }
        return $post;
    }

    /**
     * Creates or updates a post by given account and post data.
     * @param Account $account
     * @param \Fixpunkt\FpSocialBridge\v2\Data\Post $socialServerData
     * @return bool true, if the post has been changed
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function addOrUpdate(Account $account, \Fixpunkt\FpSocialBridge\v2\Data\Post $socialServerData): bool
    {
        /** @var Post $post */
        $post = $this -> findOrCreatePost($account, $socialServerData -> getId());

        // Prüfe ob der Post schon mit dem Account verbunden ist
        if (!$post -> hasAccount($account)) {
            /** @var PostLink $postLink */
            $postLink = GeneralUtility::makeInstance(PostLink::class);
            $postLink -> setAccount($account);
            $postLink -> setPost($post);
            $postLink -> setHidden(!$account -> getApprove());
            $postLink -> setPid($post -> getPid());
            $post -> addAccounts($postLink);
        }

        // Daten des Posts aktualisieren
        $post -> setMessage($socialServerData -> getMessage());
        $post -> setUrl($socialServerData -> getPostUrl());
        $post -> setUpdatedTime($socialServerData -> getUpdateTime());
        $post -> setLink($socialServerData -> getLink());
        $post -> setHeadline($socialServerData -> getHeadline());

        // Hashtags einlesen
        $hashtagObjectStorage = $this -> hashtagRepository -> findOrCreateHashtags($socialServerData -> getHashtags(), $post);
        foreach ($hashtagObjectStorage as $readHashtag) {
            if (!$post -> getHashtags() -> contains($readHashtag)) {
                $post -> addHashtag($readHashtag);
            }
        }
        foreach ($post -> getHashtags() as $existingHashtag) {
            if (!$hashtagObjectStorage -> contains($existingHashtag)) {
                $post -> removeHashtag($existingHashtag);
            }
        }

        // Mentions einlesen
        /* ToDo: Mentions auch abhängig vom Account machen!
        $post -> setMentions(new ObjectStorage());
        foreach($socialServerData -> getMentions() ?? [] as $mentionData) {
            $systemName = is_array($mentionData) ? $mentionData["systemName"] : $mentionData;
            $displayName = is_array($mentionData) ? $mentionData["displayName"] : $mentionData;

            /** @var Mention $mention *
            $mention = $this -> mentionRepository -> findOneBySystemName($systemName);
            if(!$mention) {
                $mention = new Mention();
                $mention -> setSystemName($systemName);
                $mention -> setDisplayName($displayName);
            }
            $post -> addMention($mention);
        }*/

        // make all the pictures
        $pictureObjectStorage = new ObjectStorage();
        foreach ($socialServerData -> getPictures() as $pictureUri) {
            $existingPicture = $post -> getPicturesByUri($pictureUri);
            if ($existingPicture) {
                $pictureObjectStorage -> attach($existingPicture);
            } else {
                /** @var Picture $picture */
                $picture = GeneralUtility::makeInstance(Picture::class);
                $picture -> setPost($post);
                $picture -> setUri($pictureUri);
                $picture -> setPid($post -> getPid());
                $pictureObjectStorage -> attach($picture);
            }
        }
        $post -> setPictures($pictureObjectStorage);

        if ($post -> getUid()) {
            $this -> update($post);
        } else {
            $this -> add($post);
        }

        return $post -> _isNew() || $post -> _isDirty();
    }

    /**
     * Sucht ob es einen Post für ein bestimmtes Netzwerk bereits gibt.
     * @param string $network
     * @param string $postId
     * @return QueryResultInterface
     */
    public function findByNetworkAndId(string $network, string $postId): QueryResultInterface
    {
        $query = $this -> createQuery();
        $query -> matching(
            $query -> logicalAnd(...[
                $query -> equals('network', $network),
                $query -> equals('id', $postId),
            ])
        );
        return $query -> execute();
    }

    /**
     * Sucht alle Posts mit einem gewissen Inhalt und Account.
     * @param array $search
     * @return array
     * @throws InvalidQueryException
     */
    public function search(array $search): array
    {
        $query = $this -> createQuery();
        $accounts = $search['accounts'] ?? [];
        $expression = $search['expression'] ?? '';

        if (!is_string($expression) || !$expression) {
            return [];
        }

        $constraints = [];
        $constraints[] = $query -> logicalOr(
            $query -> like('message', '%' . $expression . '%'),
            $query -> like('id', '%' . $expression . '%'),
        );
        if (is_array($accounts) && $accounts) {
            $constraints[] = $query -> in('accounts.account', $accounts);
        }

        $query -> matching(
            $query -> logicalAnd(...$constraints)
        );
        return $query -> execute() -> toArray();
    }

    /**
     * @param Post $post
     * @return int
     */
    private function getPidOfFirstLink(Post $post): int
    {
        /** @var PostLink $firstPostLink */
        $firstPostLink = $post -> getAccounts() -> current();
        return $firstPostLink -> getAccount() -> getPid();
    }
}
