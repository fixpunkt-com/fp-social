<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Utilities;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Model\Post;
use Fixpunkt\FpSocial\Domain\Model\PostLink;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Service\CacheService;

// ToDo: Clear only cache if the content actually has changed.

class CacheUtility
{
    /** @var FlexFormService  */
    protected FlexFormService $flexFormService;
    /** @var CacheService  */
    protected CacheService $cacheService;

    /**
     * @param FlexFormService $flexFormService
     * @param CacheService $cacheService
     */
    public function __construct(FlexFormService $flexFormService, CacheService $cacheService)
    {
        $this -> flexFormService = $flexFormService;
        $this -> cacheService = $cacheService;
    }

    /**
     * Clears the cache of all pages, where a given post could be displayed.
     * @param Post $post
     */
    public function clearCacheForPost(Post $post): void
    {
        $plugins = $this -> getPluginsAndConfigurations(['Wall', 'Account', 'Post']);
        $accountUids = $this -> getAccountUidsFromPosts($post);

        foreach ($plugins as $plugin) {
            if (
                $plugin['list_type'] == 'fpsocial_wall' && count($accountUids) > count(array_diff($accountUids, explode(',', ($plugin['settings']['accounts'] ?? '')))) ||
                $plugin['list_type'] == 'fpsocial_account' && in_array(($plugin['settings']['account'] ?? 0), $accountUids) ||
                $plugin['list_type'] == 'fpsocial_post' && ($plugin['settings']['post'] ?? 0) == $post -> getUid()
            ) {
                // ToDo: Check if content actually has changed
                $this -> clearPageCache($plugin['pid']);
            }
        }
    }

    /**
     * Clears the cache of all pages, where posts of a given account could be displayed.
     * @param Account $account
     */
    public function clearCacheForAccount(Account $account): void
    {
        $plugins = $this -> getPluginsAndConfigurations(['Account', 'Wall']);

        foreach ($plugins as $plugin) {
            if (
                $plugin['list_type'] == 'fpsocial_wall' && in_array($account -> getUid(), explode(',', ($plugin['settings']['accounts'] ?? ''))) ||
                $plugin['list_type'] == 'fpsocial_account' && ($plugin['settings']['account'] ?? 0) == $account -> getUid()
            ) {
                // ToDo: Check if content actually has changed
                $this -> clearPageCache($plugin['pid']);
            }
        }
    }

    /**
     * Returns a list, where a given plugin is integrated.
     * @param array $pluginNames
     * @return array
     */
    private function getPluginsAndConfigurations(array $pluginNames): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')->createQueryBuilder();

        // Get constraints for list_types
        $orConstraints = [];
        foreach ($pluginNames as $pluginName) {
            $qualifier = 'fpsocial_' . strtolower($pluginName);
            $orConstraints[] = $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter($qualifier));

        }

        // Create statement
        $statement = $queryBuilder
            -> select('uid', 'pid', 'list_type', 'pi_flexform')
            -> from('tt_content')
            -> where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                $queryBuilder -> expr() -> or(
                    ...$orConstraints
                )
            )
            -> executeQuery();

        // Catch results and enrich it.
        $results = $statement -> fetchAllAssociative();
        foreach ($results as &$result) {
            $flexFormArray = $this -> flexFormService -> convertFlexFormContentToArray($result['pi_flexform']);

            $result['settings'] = array_key_exists('settings', $flexFormArray) ? $flexFormArray['settings'] : [];
            unset($result['pi_flexform']);
        }

        return $results;
    }

    /**
     * Gets the accounts of an post.
     * @param Post $post
     * @return array
     */
    private function getAccountUidsFromPosts(Post $post): array
    {
        $accounts = new ObjectStorage();
        /** @var PostLink $postLink */
        foreach ($post -> getAccounts() as $postLink) {
            // ToDo: Move this one to Post-Model.
            $accounts -> attach($postLink -> getAccount());
        }

        $uids = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            $uids[] = $account -> getUid();
        }
        return $uids;
    }

    /**
     * Clears cache of a given site.
     * @param int $pageUid
     */
    private function clearPageCache(int $pageUid): void
    {
        $this -> cacheService -> clearPageCache($pageUid);
    }
}
