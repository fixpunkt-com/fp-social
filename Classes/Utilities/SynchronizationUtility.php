<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Utilities;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Model\Post;
use Fixpunkt\FpSocial\Domain\Repository\AccountRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostRepository;
use Fixpunkt\FpSocial\Events\AccountUpdatedEvent;
use Fixpunkt\FpSocial\Events\PostUpdatedEvent;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

class SynchronizationUtility
{
    public function __construct(
        protected readonly PostRepository $postRepository,
        protected readonly AccountRepository $accountRepository
    ) {
        // Show comments from all pages
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this -> postRepository -> setDefaultQuerySettings($querySettings);
        $this -> accountRepository -> setDefaultQuerySettings($querySettings);
    }

    /**
     * Synchronizes a whole account. Returns the number of synchronized posts.
     * @param Account $account
     * @return int
     * @throws \DateMalformedStringException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \Throwable
     */
    public function account(Account $account): int
    {
        try {
            $response = $account -> getAccess() -> getPostsData($account);
            if (is_a($response, SocialServerErrorResponse::class)) {
                throw new \Exception($response -> getMessage(), $response -> getCode());
            }
            if (!is_a($response, SocialServerPostsResponse::class)) {
                throw new \Exception('Response format not expected: ' . get_class($response) . '. Expected: ' . SocialServerPostsResponse::class, 1741293955);
            }

            // Eingelesene Posts speichern
            /** @var \Fixpunkt\FpSocialBridge\v2\Data\Post $post */
            foreach ($response -> getPosts() as $post) {
                $this -> postRepository -> addOrUpdate($account, $post);
            }

            // if a post actually got changed, clear frontend cache
            GeneralUtility::makeInstance(AccountUpdatedEvent::class, $account);
            $account -> setLastSuccessfulSynchronization(new \DateTime());
            $this -> accountRepository -> update($account);

            return count($response -> getPosts());
        } catch (\Throwable $e) {
            $account -> setSynchronizationError($e -> getMessage());
            $account -> setLastSynchronization(new \DateTime());
            $this -> accountRepository -> update($account);
            throw $e;
        }
    }

    /**
     * Synchronizes a single post. Returns true if the post was synchronized and false if the post was not found.
     * @param Account $account
     * @param Post $post
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \Throwable
     */
    public function post(Account $account, Post $post): bool
    {
        try {
            $response = $account -> getAccess() -> getPostData($account, $post);
            if (is_a($response, SocialServerErrorResponse::class)) {
                throw new \Exception($response -> getMessage(), $response -> getCode());
            }
            if (!is_a($response, SocialServerPostResponse::class)) {
                throw new \Exception('Response format not expected: ' . get_class($response) . '. Expected: ' . SocialServerPostResponse::class, 1741293955);
            }

            $post -> setOriginDeleted(false);

            $this -> postRepository -> addOrUpdate($account, $response -> getPost());
            GeneralUtility::makeInstance(PostUpdatedEvent::class, $post);
            return true;

        } catch (\Throwable $e) {
            if ($e -> getCode() == 1585047168) {
                $post -> setOriginDeleted(true);
                $this -> postRepository -> update($post);
                return false;
            }

            throw $e;
        }
    }
}
