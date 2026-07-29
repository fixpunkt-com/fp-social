<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Utilities;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Model\Post;
use Fixpunkt\FpSocial\Domain\Model\PostLink;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\CacheService;

// ToDo: Clear only cache if the content actually has changed.

class CacheUtility
{
    /**
     * CType of the "Wall" plugin (registered as a content element via ExtensionUtility).
     */
    private const CTYPE_WALL = 'fpsocial_wall';
    /**
     * CType of the single "Post" plugin.
     */
    private const CTYPE_POST = 'fpsocial_post';

    /**
     * Table that account collection identifiers point to (format "<table>:<uid>").
     */
    private const TABLE_ACCOUNT = 'tx_fpsocial_domain_model_account';
    /**
     * Table that record identifiers point to (format "<table>:<uid>").
     */
    private const TABLE_POSTLINK = 'tx_fpsocial_domain_model_postlink';

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly CacheService $cacheService
    ) {}

    /**
     * Clears the cache of all pages, where a given post could be displayed.
     * @param Post $post
     */
    public function clearCacheForPost(Post $post): void
    {
        $identifiers = $this -> getIdentifiersFromPost($post);
        $accountIdentifiers = $identifiers['accounts'];
        $recordIdentifiers = $identifiers['records'];

        foreach ($this -> getPlugins() as $plugin) {
            $matches = false;
            if ($plugin['CType'] === self::CTYPE_WALL) {
                $wallAccounts = GeneralUtility::trimExplode(',', (string)($plugin['tx_fpsocial_accounts'] ?? ''), true);
                $wallRecords = GeneralUtility::trimExplode(',', (string)($plugin['tx_fpsocial_records'] ?? ''), true);
                $matches = (bool)array_intersect($accountIdentifiers, $wallAccounts)
                    || (bool)array_intersect($recordIdentifiers, $wallRecords);
            } elseif ($plugin['CType'] === self::CTYPE_POST) {
                $selectedRecord = trim((string)($plugin['tx_fpsocial_post'] ?? ''));
                $matches = $selectedRecord !== '' && in_array($selectedRecord, $recordIdentifiers, true);
            }

            if ($matches) {
                // ToDo: Check if content actually has changed
                $this -> clearPageCache((int)$plugin['pid']);
            }
        }
    }

    /**
     * Clears the cache of all pages, where posts of a given account could be displayed.
     * @param Account $account
     */
    public function clearCacheForAccount(Account $account): void
    {
        $accountIdentifier = self::TABLE_ACCOUNT . ':' . $account -> getUid();

        foreach ($this -> getPlugins() as $plugin) {
            if ($plugin['CType'] !== self::CTYPE_WALL) {
                continue;
            }
            $wallAccounts = GeneralUtility::trimExplode(',', (string)($plugin['tx_fpsocial_accounts'] ?? ''), true);
            if (in_array($accountIdentifier, $wallAccounts, true)) {
                // ToDo: Check if content actually has changed
                $this -> clearPageCache((int)$plugin['pid']);
            }
        }
    }

    /**
     * Returns all fp_social content elements (wall + single post) with the columns
     * relevant for the cache invalidation.
     * @return array
     */
    private function getPlugins(): array
    {
        $queryBuilder = $this -> connectionPool -> getConnectionForTable('tt_content') -> createQueryBuilder();

        return $queryBuilder
            -> select('uid', 'pid', 'CType', 'tx_fpsocial_accounts', 'tx_fpsocial_records', 'tx_fpsocial_post')
            -> from('tt_content')
            -> where(
                $queryBuilder -> expr() -> in(
                    'CType',
                    $queryBuilder -> createNamedParameter(
                        [self::CTYPE_WALL, self::CTYPE_POST],
                        Connection::PARAM_STR_ARRAY
                    )
                )
            )
            -> executeQuery()
            -> fetchAllAssociative();
    }

    /**
     * Collects the collection/record identifiers ("<table>:<uid>") a post is reachable through,
     * so they can be matched against the identifier lists stored in the plugin content elements.
     * @param Post $post
     * @return array{accounts: string[], records: string[]}
     */
    private function getIdentifiersFromPost(Post $post): array
    {
        $accountIdentifiers = [];
        $recordIdentifiers = [];

        /** @var PostLink $postLink */
        foreach ($post -> getAccounts() ?? [] as $postLink) {
            $recordIdentifiers[] = self::TABLE_POSTLINK . ':' . $postLink -> getUid();

            $account = $postLink -> getAccount();
            $accountIdentifiers[$account -> getUid()] = self::TABLE_ACCOUNT . ':' . $account -> getUid();
        }

        return [
            'accounts' => array_values($accountIdentifiers),
            'records' => $recordIdentifiers,
        ];
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
