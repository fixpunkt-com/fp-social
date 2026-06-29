<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Repository;

use Fixpunkt\FpSocial\Domain\Model\Hashtag;
use Fixpunkt\FpSocial\Domain\Model\Post;
use Fixpunkt\FpSocial\Domain\Model\PostLink;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class HashtagRepository extends Repository
{
    /** @var array  */
    private array $newlyCreatedHashtags = [];

    /**
     * Findet Hashtags mit Hilfe ihrer Uids.
     * @param array $uids
     * @return QueryResultInterface
     */
    public function findByUids(array $uids): QueryResultInterface
    {
        $query = $this -> createQuery();
        $query -> setQuerySettings($query -> getQuerySettings() -> setRespectStoragePage(false));
        $query -> matching(
            $query -> in('uid', $uids)
        );
        return $query -> execute();
    }

    /**
     * Findet Hashtags anhand eines Arrays von Hashtags.
     * @param array $hashtags
     * @param Post $post
     * @return ObjectStorage<Hashtag>
     */
    public function findOrCreateHashtags(array $hashtags, Post $post): ObjectStorage
    {
        $hashtags = $this -> hashtagsToLower($hashtags);
        $existingHashtags = $this -> findExistingHashtags($hashtags);

        // In ObjectStorage überführen und gleichzeitig aus Hashtag-Array löschen
        /** @var ObjectStorage<Hashtag> $objectStorage */
        $objectStorage = new ObjectStorage();
        /** @var Hashtag $hashtag */
        foreach ($existingHashtags as $hashtag) {
            $arraykey = array_search($hashtag -> getHashtag(), $hashtags);
            if ($arraykey !== false) {
                $objectStorage -> attach($hashtag);
                unset($hashtags[$arraykey]);
            }
        }

        // Neue Hashtags anlegen
        $pid = $this -> getPidOfFirstLink($post);
        /** @var string $hashtag */
        foreach ($hashtags as $hashtag) {
            $objectStorage -> attach(
                $this -> createNewHashtag($hashtag, $pid)
            );
        }

        return $objectStorage;
    }

    /**
     * Findet bereits existierende Hashtags aus einer Liste von Hashtags.
     * @param array $hashtags
     * @return ObjectStorage<Hashtag>
     */
    private function findExistingHashtags(array $hashtags): ObjectStorage
    {
        /** @var ObjectStorage<Hashtag> $objectStorage */
        $objectStorage = new ObjectStorage();
        if (!$hashtags) {
            return $objectStorage;
        }

        // Existierende Hashtags durchsuchen
        $query = $this -> createQuery();
        $query -> setQuerySettings($query -> getQuerySettings() -> setRespectStoragePage(false));

        // Bereits existierende Hashtags finden
        $or = [];
        /** @var string $hashtag */
        foreach ($hashtags as $hashtag) {
            $or[] = $query -> equals('hashtag', $hashtag);
        }
        $query -> matching(
            $query -> logicalOr(...$or)
        );
        /** @var Hashtag $hashtag */
        foreach ($query -> execute() as $hashtag) {
            $objectStorage -> attach($hashtag);
        }

        // Neu angelegte Hashtags durchsuchen
        /** @var string $hashtag */
        foreach ($hashtags as $hashtag) {
            if (array_key_exists($hashtag, $this -> newlyCreatedHashtags)) {
                $objectStorage -> attach($this -> newlyCreatedHashtags[$hashtag]);
            }
        }

        return $objectStorage;
    }

    /**
     * Erstellt einen neuen Hashtag.
     * @param string $hashtag
     * @param int $pid
     * @return Hashtag
     */
    private function createNewHashtag(string $hashtag, int $pid): Hashtag
    {
        $newHashtag = new Hashtag();
        $newHashtag -> setHashtag($hashtag);
        $newHashtag -> setPid($pid);

        $this -> newlyCreatedHashtags[$hashtag] = $newHashtag;
        return $newHashtag;
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

    /**
     * Macht alle Hashtags zu Lowercase
     * @param array $hashtags
     * @return array
     */
    private function hashtagsToLower(array $hashtags): array
    {
        $lowercase = [];
        /** @var string $hashtag */
        foreach ($hashtags as $hashtag) {
            $hashtag = strtolower($hashtag);
            $lowercase[$hashtag] = $hashtag;
        }
        return array_values($lowercase);
    }
}
