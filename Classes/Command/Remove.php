<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Command;

use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Remove extends Command
{
    // ToDo: Sitecache!

    /** @var int[] */
    protected array $accountUids = [];
    /** @var \DateTime|null  */
    protected ?\DateTime $datetime = null;
    /** @var array  */
    protected array $postUids = [];

    /**
     * Konfiguriert das Kommando.
     */
    protected function configure()
    {
        $this
            -> setDescription('Deletes old posts')
            -> setHelp('This Task removes old posts from a selected account that are older than a specified number of days.')
            -> addArgument(
                'days',
                InputArgument::REQUIRED,
                'Number of days after which posts will be deleted.'
            )
            /**
            -> addArgument(
                'accountUid',
                InputArgument::OPTIONAL,
                'Uid of the account the posts should be deleted from.'
            )*/;
    }

    /**
     * Setzt die Variablen.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Account Uid einlesen
        //$this -> accountUids = $input -> hasArgument("accountUid") && $input -> getArgument('accountUid') ? explode(",", $input -> getArgument('accountUid')) : [];
        $this -> accountUids = [];

        // Tage einlesen
        $days = $input -> getArgument('days');
        $dateInterval = new \DateInterval('P' . $days . 'D');
        $this -> datetime = (new \DateTime()) -> sub($dateInterval);
    }

    /**
     * Führt das Kommando aus und synchronisiert Accounts.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // delete posts which are older than given time and not in use
        $allPostUids = $this -> getPostUids();
        $usedPostUids = $this -> getPostUidsInUse($allPostUids);
        $this -> postUids = array_diff($allPostUids, $usedPostUids);
        $this -> deletePosts();

        // delete unreferenced pictures and hashtags
        $this -> deletePictures();
        $this -> deleteHashtags();

        return 0;
    }

    /**
     * reads all relevant post uids
     * @throws Exception
     */
    protected function getPostUids(): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_post');
        $queryBuilder
            -> select('post.uid')
            -> from('tx_fpsocial_domain_model_post', 'post')
            -> where(
                $queryBuilder->expr()->lte('post.updated_time', $queryBuilder->createNamedParameter($this -> datetime -> format('Y-m-d H:i:s'), Connection::PARAM_STR))
            )
            -> groupBy('post.uid');

        if ($this -> accountUids) {
            // if account uids are given, only check those
            $queryBuilder
                -> join(
                    'post',
                    'tx_fpsocial_domain_model_postlink',
                    'postlink',
                    $queryBuilder->expr()->eq('postlink.post', $queryBuilder->quoteIdentifier('post.uid'))
                )
                -> join(
                    'postlink',
                    'tx_fpsocial_domain_model_account',
                    'account',
                    $queryBuilder->expr()->eq('account.uid', $queryBuilder->quoteIdentifier('postlink.account'))
                )
                -> andWhere(
                    $queryBuilder->expr()->in('account.uid', $queryBuilder->createNamedParameter($this -> accountUids, Connection::PARAM_INT_ARRAY)),
                );
        }

        $statement = $queryBuilder -> executeQuery();
        return $statement -> fetchFirstColumn();
    }

    /**
     * returns all post uids which are in use right now from a given list of post uids.
     */
    private function getPostUidsInUse(array $postUids): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        // get post links
        $queryBuilder = $connectionPool -> getQueryBuilderForTable('tx_fpsocial_domain_model_postlink');
        $postLinkUids = $queryBuilder
            -> select('uid')
            -> from('tx_fpsocial_domain_model_postlink')
            -> where(
                $queryBuilder -> expr() -> in('post', $queryBuilder -> createNamedParameter($postUids, Connection::PARAM_INT_ARRAY)),
            )
            -> executeQuery()
            -> fetchFirstColumn();

        // get all post links in use
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool -> getQueryBuilderForTable('tt_content');
        // create "or" constraints
        $or = [];
        /** @var int $postLinkUid */
        foreach ($postLinkUids as $postLinkUid) {
            $or[] = $queryBuilder -> expr() -> eq('tx_fpsocial_post', $queryBuilder->createNamedParameter('tx_fpsocial_domain_model_postlink:' . $postLinkUid));
        }
        $postLinkIdentifiersInUse = $queryBuilder
            -> select('tx_fpsocial_post')
            -> from('tt_content')
            -> where(
                $queryBuilder -> expr() -> eq('CType', $queryBuilder->createNamedParameter('fpsocial_post')),
                $queryBuilder -> expr() -> or(...$or),
            )
            -> groupBy('tx_fpsocial_post')
            -> executeQuery()
            -> fetchFirstColumn();

        // collect post uids
        $postLinkUidsInUse = [];
        foreach ($postLinkIdentifiersInUse as $postLinkIdentifier) {
            $postLinkUidsInUse[] = str_replace('tx_fpsocial_domain_model_postlink:', '', $postLinkIdentifier);
        }
        $queryBuilder = $connectionPool -> getQueryBuilderForTable('tx_fpsocial_domain_model_postlink');
        return $queryBuilder
            -> select('post')
            -> from('tx_fpsocial_domain_model_postlink')
            -> where(
                $queryBuilder -> expr() -> in('uid', $queryBuilder -> createNamedParameter($postLinkUidsInUse, Connection::PARAM_INT_ARRAY)),
            )
            -> groupBy('post')
            -> executeQuery()
            -> fetchFirstColumn();
    }

    /**
     * Löscht Posts und deren Post Links
     */
    private function deletePosts(): void
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_post');
        $queryBuilder
            -> delete('tx_fpsocial_domain_model_post')
            -> where(
                $queryBuilder -> expr() -> in('uid', $queryBuilder -> createNamedParameter($this -> postUids, Connection::PARAM_INT_ARRAY)),
            )
            -> executeStatement();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_postlink');
        $queryBuilder
            -> delete('tx_fpsocial_domain_model_postlink')
            -> where(
                $queryBuilder -> expr() -> in('post', $queryBuilder -> createNamedParameter($this -> postUids, Connection::PARAM_INT_ARRAY)),
            )
            -> executeStatement();
    }

    /**
     * Entfernt Picture Objekte.
     */
    private function deletePictures(): void
    {
        // get remaining post uids
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_post');
        $postUids = $queryBuilder
            -> select('uid')
            -> from('tx_fpsocial_domain_model_post')
            -> executeQuery()
            -> fetchFirstColumn();

        // delete picture elements
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_picture');
        $queryBuilder
            -> delete('tx_fpsocial_domain_model_picture')
            -> where(
                $queryBuilder -> expr() -> notIn('post', $queryBuilder -> createNamedParameter($postUids, Connection::PARAM_INT_ARRAY))
            )
            -> executeStatement();

        // delete actual files
        $this -> deleteFiles();
    }

    /**
     * Entfernt die mit den Bildern verbundenen Dateien.
     */
    private function deleteFiles(): void
    {
        // get remaining images
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_picture');
        $pictureUids = $queryBuilder
            -> select('uid')
            -> from('tx_fpsocial_domain_model_picture')
            -> executeQuery()
            -> fetchFirstColumn();

        // get unreferenced file references
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
        $fileUids = $queryBuilder
            -> select('uid_local')
            -> from('sys_file_reference')
            -> where(
                $queryBuilder -> expr() -> notIn('uid_foreign', $queryBuilder -> createNamedParameter($pictureUids, Connection::PARAM_INT_ARRAY)),
                $queryBuilder -> expr() -> eq('tablenames', $queryBuilder -> createNamedParameter('tx_fpsocial_domain_model_picture', Connection::PARAM_STR)),
                $queryBuilder -> expr() -> eq('fieldname', $queryBuilder -> createNamedParameter('filereference', Connection::PARAM_STR))
            )
            -> groupBy('uid')
            -> executeQuery()
            -> fetchFirstColumn();

        // delete files
        $fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
        /** @var int $fileUid */
        foreach ($fileUids as $fileUid) {
            try {
                $file = $fileRepository->findByUid($fileUid);
                $file -> delete();
            } catch (\RuntimeException $e) {
            }
        }

        // delete references
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            -> delete('sys_file_reference')
            -> where(
                $queryBuilder -> expr() -> notIn('uid_foreign', $queryBuilder -> createNamedParameter($pictureUids, Connection::PARAM_INT_ARRAY)),
                $queryBuilder -> expr() -> eq('tablenames', $queryBuilder -> createNamedParameter('tx_fpsocial_domain_model_picture', Connection::PARAM_STR)),
                $queryBuilder -> expr() -> eq('fieldname', $queryBuilder -> createNamedParameter('filereference', Connection::PARAM_STR))
            )
            -> executeStatement();

    }

    /**
     * Entfernt Picture Objekte.
     */
    private function deleteHashtags(): void
    {
        // get remaining post uids
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_post');
        $postUids = $queryBuilder
            -> select('uid')
            -> from('tx_fpsocial_domain_model_post')
            -> executeQuery()
            -> fetchFirstColumn();

        // delete unreferenced relations
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_post_hashtag_mm');
        $queryBuilder
            -> delete('tx_fpsocial_post_hashtag_mm')
            -> where(
                $queryBuilder -> expr() -> notIn('uid_local', $queryBuilder -> createNamedParameter($postUids, Connection::PARAM_INT_ARRAY))
            )
            -> executeStatement();

        // delete unreferenced hashtags
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_post_hashtag_mm');
        $usedHashtagUids = $queryBuilder
            -> select('uid_foreign')
            -> from('tx_fpsocial_post_hashtag_mm')
            -> groupBy('uid_foreign')
            -> executeQuery()
            -> fetchFirstColumn();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_hashtag');
        $queryBuilder
            -> delete('tx_fpsocial_domain_model_hashtag')
            -> where(
                $queryBuilder -> expr() -> notIn('uid', $queryBuilder -> createNamedParameter($usedHashtagUids, Connection::PARAM_INT_ARRAY))
            )
            -> executeStatement();
    }
}
