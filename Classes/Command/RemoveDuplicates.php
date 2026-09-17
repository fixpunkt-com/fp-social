<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoveDuplicates extends Command
{
    private const TABLE_POST = 'tx_fpsocial_domain_model_post';
    private const TABLE_POSTLINK = 'tx_fpsocial_domain_model_postlink';
    private const TABLE_ACCOUNT = 'tx_fpsocial_domain_model_account';
    private const TABLE_PICTURE = 'tx_fpsocial_domain_model_picture';
    private const TABLE_HASHTAG = 'tx_fpsocial_domain_model_hashtag';
    private const TABLE_HASHTAG_MM = 'tx_fpsocial_post_hashtag_mm';
    private const TABLE_MENTION_MM = 'tx_fpsocial_post_mention_mm';

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * Konfiguriert das Kommando.
     */
    protected function configure()
    {
        $this
            -> setDescription('Removes posts that were imported a second time while they were hidden.')
            -> setHelp(
                'Until version 17.7.2 the synchronization did not find hidden posts and imported them' . PHP_EOL .
                'again as a visible copy. This command removes those copies and keeps the oldest record' . PHP_EOL .
                'of each post, which is the one that carries the decision of the editor.' . PHP_EOL .
                'Run it once after the update, otherwise the next synchronization creates the copies again.' . PHP_EOL .
                'Without --force nothing is changed.'
            )
            -> addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Apply the changes. Without this option the command only reports what it would do.'
            );
    }

    /**
     * Führt das Kommando aus und entfernt doppelt importierte Posts.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = (bool)$input -> getOption('force');
        $references = $this -> getPostLinkReferences();
        $groups = $this -> getDuplicateGroups();

        // Ein Post kann in mehreren Netzwerkgruppen stecken und darf dort nicht gelöscht werden,
        // wo er in einer anderen Gruppe der behaltene Datensatz ist
        $keptUids = [];
        foreach ($groups as $group) {
            $keptUids[min($group['uids'])] = true;
        }

        $plans = [];
        $skipped = [];
        foreach ($groups as $group) {
            $plan = $this -> planGroup($group, $keptUids, $references);
            if ($plan === null) {
                continue;
            }
            if ($plan['blocked']) {
                $skipped[] = $plan;
                continue;
            }
            $plans[] = $plan;
        }

        $output -> writeln($force
            ? '<info>Removing duplicated posts...</info>'
            : '<info>Dry run - nothing is changed. Add --force to apply.</info>');
        $output -> writeln('-----------------------------------------------------------');

        $hashtagUids = [];
        $removedFiles = 0;
        foreach ($plans as $plan) {
            $output -> writeln(sprintf(
                '%s %s: keep post %d%s, remove post %s',
                $this -> networkLabel($plan['network']),
                $plan['id'],
                $plan['keepUid'],
                $plan['keepHidden'] ? ' (hidden)' : '',
                implode(', ', $plan['postUids'])
            ));
            if ($plan['moveLinks']) {
                $output -> writeln(sprintf(
                    '    post link %s of another account is moved to post %d',
                    implode(', ', $plan['moveLinks']),
                    $plan['keepUid']
                ));
            }
            $hashtagUids = array_merge($hashtagUids, $plan['hashtagUids']);

            if ($force) {
                $removedFiles += $this -> removeGroup($plan);
            }
        }

        foreach ($skipped as $plan) {
            $output -> writeln(sprintf(
                '<comment>skipped %s %s: %s</comment>',
                $this -> networkLabel($plan['network']),
                $plan['id'],
                implode('; ', $plan['blocked'])
            ));
        }

        $removedHashtags = $force ? $this -> removeOrphanedHashtags(array_unique($hashtagUids)) : 0;

        $output -> writeln('-----------------------------------------------------------');
        $output -> writeln(sprintf('Duplicate groups:        %6d', count($plans)));
        $output -> writeln(sprintf('Posts:                   %6d', $this -> sumOf($plans, 'postUids')));
        $output -> writeln(sprintf('Post links removed:      %6d', $this -> sumOf($plans, 'deleteLinks')));
        $output -> writeln(sprintf('Post links moved:        %6d', $this -> sumOf($plans, 'moveLinks')));
        $output -> writeln(sprintf('Pictures:                %6d', $this -> sumOf($plans, 'pictureUids')));
        if ($force) {
            $output -> writeln(sprintf('Files deleted:           %6d', $removedFiles));
            $output -> writeln(sprintf('Hashtags deleted:        %6d', $removedHashtags));
        }
        if ($skipped) {
            $output -> writeln(sprintf('<comment>Groups skipped:          %6d</comment>', count($skipped)));
        }
        $postsWithoutLink = $this -> countPostsWithoutLink();
        if ($postsWithoutLink) {
            $output -> writeln(sprintf('Posts without post link: %6d (not touched)', $postsWithoutLink));
        }
        $output -> writeln('-----------------------------------------------------------');

        if (!$plans && !$skipped) {
            $output -> writeln('<info>No duplicates found.</info>');
            return 0;
        }
        if ($force) {
            $output -> writeln('<info>Done. Please flush the frontend caches.</info>');
        } else {
            $output -> writeln('Nothing has been changed. Run the command again with --force to apply.');
        }
        return 0;
    }

    /**
     * Entscheidet für eine Duplikat-Gruppe, was behalten, verschoben und gelöscht wird.
     * @param array $group
     * @param array $keptUids
     * @param array $references
     * @return array|null
     */
    private function planGroup(array $group, array $keptUids, array $references): ?array
    {
        $uids = $group['uids'];
        sort($uids);
        $keepUid = (int)array_shift($uids);

        $postUids = [];
        foreach ($uids as $uid) {
            if (!isset($keptUids[$uid])) {
                $postUids[] = (int)$uid;
            }
        }
        if (!$postUids) {
            return null;
        }

        $accountLinks = [];
        $keepHidden = true;
        foreach ($this -> getPostLinks([$keepUid]) as $link) {
            $accountLinks[(int)$link['account']] = (int)$link['uid'];
            if (!$link['hidden']) {
                $keepHidden = false;
            }
        }

        $deleteLinks = [];
        $moveLinks = [];
        $blocked = [];
        foreach ($this -> getPostLinks($postUids) as $link) {
            $linkUid = (int)$link['uid'];
            $accountUid = (int)$link['account'];

            // Kennt der behaltene Post den Account noch nicht, wird die Verbindung umgehängt
            if (!isset($accountLinks[$accountUid])) {
                $accountLinks[$accountUid] = $linkUid;
                $moveLinks[] = $linkUid;
                continue;
            }
            if (isset($references[$linkUid])) {
                $blocked[] = 'post link ' . $linkUid . ' is used by ' . implode(', ', $references[$linkUid]);
                continue;
            }
            $deleteLinks[] = $linkUid;
        }

        return [
            'network' => $group['network'],
            'id' => $group['id'],
            'keepUid' => $keepUid,
            'keepHidden' => $keepHidden,
            'postUids' => $postUids,
            'deleteLinks' => $deleteLinks,
            'moveLinks' => $moveLinks,
            'pictureUids' => $this -> getPictureUids($postUids),
            'hashtagUids' => $this -> getHashtagUids($postUids),
            'blocked' => $blocked,
        ];
    }

    /**
     * Löscht eine geplante Gruppe und gibt die Anzahl gelöschter Dateien zurück.
     * @param array $plan
     * @return int
     */
    private function removeGroup(array $plan): int
    {
        $fileUids = $this -> getFileUids($plan['pictureUids']);

        $connection = $this -> connectionPool -> getConnectionForTable(self::TABLE_POST);
        $connection -> transactional(function () use ($plan): void {
            $this -> deleteFileReferences($plan['pictureUids']);
            $this -> deleteWhereIn(self::TABLE_PICTURE, 'post', $plan['postUids']);
            $this -> deleteWhereIn(self::TABLE_HASHTAG_MM, 'uid_local', $plan['postUids']);
            $this -> deleteWhereIn(self::TABLE_MENTION_MM, 'uid_local', $plan['postUids']);
            $this -> moveLinks($plan['moveLinks'], $plan['keepUid']);
            $this -> deleteWhereIn(self::TABLE_POSTLINK, 'uid', $plan['deleteLinks']);
            $this -> deleteWhereIn(self::TABLE_POST, 'uid', $plan['postUids']);
        });

        // Dateien erst nach der Transaktion, ein Rollback könnte sie nicht zurückholen
        return $this -> deleteFiles($fileUids);
    }

    /**
     * Liest alle Posts, die sich Netzwerk und Post-Id mit einem anderen Post teilen.
     * @return array
     */
    private function getDuplicateGroups(): array
    {
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable(self::TABLE_POST);
        // Das hidden-Flag auf dem PostLink ist die Ursache der Duplikate und darf hier nicht filtern
        $queryBuilder -> getRestrictions()
            -> removeAll()
            -> add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $rows = $queryBuilder
            -> select('post.uid', 'post.id', 'account.network')
            -> from(self::TABLE_POST, 'post')
            -> join(
                'post',
                self::TABLE_POSTLINK,
                'postlink',
                $queryBuilder -> expr() -> eq('postlink.post', $queryBuilder -> quoteIdentifier('post.uid'))
            )
            -> join(
                'postlink',
                self::TABLE_ACCOUNT,
                'account',
                $queryBuilder -> expr() -> eq('account.uid', $queryBuilder -> quoteIdentifier('postlink.account'))
            )
            -> groupBy('post.uid', 'post.id', 'account.network')
            -> orderBy('account.network')
            -> addOrderBy('post.id')
            -> addOrderBy('post.uid')
            -> executeQuery()
            -> fetchAllAssociative();

        $groups = [];
        foreach ($rows as $row) {
            $key = $row['network'] . '|' . $row['id'];
            $groups[$key]['network'] = (string)$row['network'];
            $groups[$key]['id'] = (string)$row['id'];
            $groups[$key]['uids'][] = (int)$row['uid'];
        }

        return array_values(array_filter($groups, static fn(array $group): bool => count($group['uids']) > 1));
    }

    /**
     * Sammelt die PostLinks, die in Plugins referenziert sind.
     * @return array
     */
    private function getPostLinkReferences(): array
    {
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable('tt_content');
        $queryBuilder -> getRestrictions()
            -> removeAll()
            -> add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $rows = $queryBuilder
            -> select('uid', 'tx_fpsocial_post', 'tx_fpsocial_records')
            -> from('tt_content')
            -> where(
                $queryBuilder -> expr() -> or(
                    $queryBuilder -> expr() -> neq('tx_fpsocial_post', $queryBuilder -> createNamedParameter('')),
                    $queryBuilder -> expr() -> neq('tx_fpsocial_records', $queryBuilder -> createNamedParameter('')),
                )
            )
            -> executeQuery()
            -> fetchAllAssociative();

        $prefix = self::TABLE_POSTLINK . ':';
        $references = [];
        foreach ($rows as $row) {
            foreach (['tx_fpsocial_post', 'tx_fpsocial_records'] as $field) {
                foreach (GeneralUtility::trimExplode(',', (string)($row[$field] ?? ''), true) as $identifier) {
                    if (!str_starts_with($identifier, $prefix)) {
                        continue;
                    }
                    $linkUid = (int)substr($identifier, strlen($prefix));
                    $references[$linkUid][] = 'tt_content ' . $row['uid'] . ' (' . $field . ')';
                }
            }
        }
        return $references;
    }

    /**
     * @param array $postUids
     * @return array
     */
    private function getPostLinks(array $postUids): array
    {
        if (!$postUids) {
            return [];
        }
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable(self::TABLE_POSTLINK);
        $queryBuilder -> getRestrictions() -> removeAll();

        return $queryBuilder
            -> select('uid', 'post', 'account', 'hidden')
            -> from(self::TABLE_POSTLINK)
            -> where(
                $queryBuilder -> expr() -> in(
                    'post',
                    $queryBuilder -> createNamedParameter($postUids, Connection::PARAM_INT_ARRAY)
                )
            )
            -> orderBy('uid')
            -> executeQuery()
            -> fetchAllAssociative();
    }

    /**
     * @param array $postUids
     * @return array
     */
    private function getPictureUids(array $postUids): array
    {
        return $this -> fetchColumn(self::TABLE_PICTURE, 'uid', 'post', $postUids);
    }

    /**
     * @param array $postUids
     * @return array
     */
    private function getHashtagUids(array $postUids): array
    {
        return $this -> fetchColumn(self::TABLE_HASHTAG_MM, 'uid_foreign', 'uid_local', $postUids);
    }

    /**
     * @param array $pictureUids
     * @return array
     */
    private function getFileUids(array $pictureUids): array
    {
        if (!$pictureUids) {
            return [];
        }
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable('sys_file_reference');
        $queryBuilder -> getRestrictions() -> removeAll();

        return $queryBuilder
            -> select('uid_local')
            -> from('sys_file_reference')
            -> where(
                $queryBuilder -> expr() -> in(
                    'uid_foreign',
                    $queryBuilder -> createNamedParameter($pictureUids, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder -> expr() -> eq(
                    'tablenames',
                    $queryBuilder -> createNamedParameter(self::TABLE_PICTURE)
                ),
                $queryBuilder -> expr() -> eq(
                    'fieldname',
                    $queryBuilder -> createNamedParameter('filereference')
                )
            )
            -> groupBy('uid_local')
            -> executeQuery()
            -> fetchFirstColumn();
    }

    /**
     * @param array $pictureUids
     */
    private function deleteFileReferences(array $pictureUids): void
    {
        if (!$pictureUids) {
            return;
        }
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            -> delete('sys_file_reference')
            -> where(
                $queryBuilder -> expr() -> in(
                    'uid_foreign',
                    $queryBuilder -> createNamedParameter($pictureUids, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder -> expr() -> eq(
                    'tablenames',
                    $queryBuilder -> createNamedParameter(self::TABLE_PICTURE)
                ),
                $queryBuilder -> expr() -> eq(
                    'fieldname',
                    $queryBuilder -> createNamedParameter('filereference')
                )
            )
            -> executeStatement();
    }

    /**
     * Löscht die Dateien, auf die keine Referenz mehr zeigt.
     * @param array $fileUids
     * @return int
     */
    private function deleteFiles(array $fileUids): int
    {
        $deleted = 0;
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        foreach ($fileUids as $fileUid) {
            $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable('sys_file_reference');
            $queryBuilder -> getRestrictions() -> removeAll();
            $stillUsed = $queryBuilder
                -> count('uid')
                -> from('sys_file_reference')
                -> where(
                    $queryBuilder -> expr() -> eq(
                        'uid_local',
                        $queryBuilder -> createNamedParameter((int)$fileUid, Connection::PARAM_INT)
                    )
                )
                -> executeQuery()
                -> fetchOne();
            if ($stillUsed) {
                continue;
            }
            try {
                $fileRepository -> findByUid((int)$fileUid) -> delete();
                $deleted++;
            } catch (\RuntimeException $e) {
            }
        }
        return $deleted;
    }

    /**
     * Hängt PostLinks an den behaltenen Post um.
     * @param array $linkUids
     * @param int $keepUid
     */
    private function moveLinks(array $linkUids, int $keepUid): void
    {
        if (!$linkUids) {
            return;
        }
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable(self::TABLE_POSTLINK);
        $queryBuilder
            -> update(self::TABLE_POSTLINK)
            -> set('post', $keepUid, true, Connection::PARAM_INT)
            -> where(
                $queryBuilder -> expr() -> in(
                    'uid',
                    $queryBuilder -> createNamedParameter($linkUids, Connection::PARAM_INT_ARRAY)
                )
            )
            -> executeStatement();
    }

    /**
     * Entfernt Hashtags, die weder an einem Post noch in einem Plugin hängen.
     * @param array $hashtagUids
     * @return int
     */
    private function removeOrphanedHashtags(array $hashtagUids): int
    {
        if (!$hashtagUids) {
            return 0;
        }
        $stillUsed = $this -> fetchColumn(self::TABLE_HASHTAG_MM, 'uid_foreign', 'uid_foreign', $hashtagUids);
        $orphans = array_diff($hashtagUids, $stillUsed, $this -> getHashtagUidsInPlugins());
        if (!$orphans) {
            return 0;
        }
        $this -> deleteWhereIn(self::TABLE_HASHTAG, 'uid', $orphans);
        return count($orphans);
    }

    /**
     * Liest die Hashtags, die in einem Wall-Plugin ausgewählt sind.
     * @return array
     */
    private function getHashtagUidsInPlugins(): array
    {
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable('tt_content');
        $queryBuilder -> getRestrictions()
            -> removeAll()
            -> add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $values = $queryBuilder
            -> select('tx_fpsocial_hashtags')
            -> from('tt_content')
            -> where(
                $queryBuilder -> expr() -> neq('tx_fpsocial_hashtags', $queryBuilder -> createNamedParameter(''))
            )
            -> executeQuery()
            -> fetchFirstColumn();

        $uids = [];
        foreach ($values as $value) {
            foreach (GeneralUtility::trimExplode(',', (string)$value, true) as $uid) {
                $uids[] = (int)$uid;
            }
        }
        return $uids;
    }

    /**
     * @return int
     */
    private function countPostsWithoutLink(): int
    {
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable(self::TABLE_POST);
        $queryBuilder -> getRestrictions() -> removeAll();
        $linkedPostUids = $this -> connectionPool
            -> getQueryBuilderForTable(self::TABLE_POSTLINK)
            -> select('post')
            -> from(self::TABLE_POSTLINK)
            -> groupBy('post')
            -> executeQuery()
            -> fetchFirstColumn();

        if (!$linkedPostUids) {
            return (int)$queryBuilder -> count('uid') -> from(self::TABLE_POST) -> executeQuery() -> fetchOne();
        }

        return (int)$queryBuilder
            -> count('uid')
            -> from(self::TABLE_POST)
            -> where(
                $queryBuilder -> expr() -> notIn(
                    'uid',
                    $queryBuilder -> createNamedParameter($linkedPostUids, Connection::PARAM_INT_ARRAY)
                )
            )
            -> executeQuery()
            -> fetchOne();
    }

    /**
     * @param string $table
     * @param string $select
     * @param string $field
     * @param array $values
     * @return array
     */
    private function fetchColumn(string $table, string $select, string $field, array $values): array
    {
        if (!$values) {
            return [];
        }
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable($table);
        $queryBuilder -> getRestrictions() -> removeAll();

        return array_map('intval', $queryBuilder
            -> select($select)
            -> from($table)
            -> where(
                $queryBuilder -> expr() -> in(
                    $field,
                    $queryBuilder -> createNamedParameter($values, Connection::PARAM_INT_ARRAY)
                )
            )
            -> groupBy($select)
            -> executeQuery()
            -> fetchFirstColumn());
    }

    /**
     * @param string $table
     * @param string $field
     * @param array $values
     */
    private function deleteWhereIn(string $table, string $field, array $values): void
    {
        if (!$values) {
            return;
        }
        $queryBuilder = $this -> connectionPool -> getQueryBuilderForTable($table);
        $queryBuilder
            -> delete($table)
            -> where(
                $queryBuilder -> expr() -> in(
                    $field,
                    $queryBuilder -> createNamedParameter($values, Connection::PARAM_INT_ARRAY)
                )
            )
            -> executeStatement();
    }

    /**
     * Das Netzwerk steht als Klassenname in der Datenbank, im Report genügt der kurze Name.
     * @param string $network
     * @return string
     */
    private function networkLabel(string $network): string
    {
        $short = strrchr($network, '\\');
        return $short === false ? $network : substr($short, 1);
    }

    /**
     * @param array $plans
     * @param string $key
     * @return int
     */
    private function sumOf(array $plans, string $key): int
    {
        $sum = 0;
        foreach ($plans as $plan) {
            $sum += count($plan[$key]);
        }
        return $sum;
    }
}
