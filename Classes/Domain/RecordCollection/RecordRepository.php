<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\RecordCollection;

use Fixpunkt\FpSocial\Domain\Interfaces\RecordInterface;
use Fixpunkt\FpSocial\Domain\Interfaces\RecordRepositoryInterface;
use Fixpunkt\FpSocial\Domain\Repository\PostLinkRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class RecordRepository implements RecordRepositoryInterface
{
    /** @var PostLinkRepository  */
    protected PostLinkRepository $postLinkRepository;
    public function __construct()
    {
        $this -> postLinkRepository = GeneralUtility::makeInstance(PostLinkRepository::class);
    }

    public function getCollectionsForTca(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_fpsocial_domain_model_account')->createQueryBuilder();
        $accounts = $queryBuilder
            -> select('uid', 'network')
            -> from('tx_fpsocial_domain_model_account')
            -> executeQuery() -> fetchAllAssociative();

        $items = [];
        foreach ($accounts as $account) {
            $class = '\\' . $account['network'];

            if (class_exists($class)) {
                $items[] = [
                    'label' => $class::getTCALabelAccount($account['uid']),
                    'value' => 'tx_fpsocial_domain_model_account:' . $account['uid'],
                ];
            }
        }

        return $items;
    }

    public function getAllRecordsForTca(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_fpsocial_domain_model_postlink')->createQueryBuilder();
        $postLinkData = $queryBuilder
            -> select('postlink.uid', 'account.network', 'post.id')
            -> from('tx_fpsocial_domain_model_postlink', 'postlink')
            -> join(
                'postlink',
                'tx_fpsocial_domain_model_account',
                'account',
                $queryBuilder->expr()->eq('account.uid', $queryBuilder->quoteIdentifier('postlink.account'))
            )
            -> join(
                'postlink',
                'tx_fpsocial_domain_model_post',
                'post',
                $queryBuilder->expr()->eq('post.uid', $queryBuilder->quoteIdentifier('postlink.post'))
            )
            -> executeQuery() -> fetchAllAssociative();

        $items = [];
        foreach ($postLinkData as $data) {
            $class = '\\' . $data['network'];
            if (class_exists($class)) {
                $items[] = [
                    'label' => $class::DESCRIPTION . ': ' . $data['id'],
                    'value' => 'tx_fpsocial_domain_model_postlink:' . $data['uid'],
                ];
            }
        }
        return $items;
    }

    public function getRecordsByIdentifiers(array $identifiers): array
    {
        $query = $this -> postLinkRepository -> createQuery();
        $query -> matching(
            $query -> in('uid', $identifiers)
        );
        return $query -> execute() -> toArray();
    }

    /**
     * Get postlink records.
     * @param array $filter
     * @param int $limit
     * @param ?RecordInterface $referenceRecord
     */
    public function getRecordsByFilter(array $filter, int $limit, ?RecordInterface $referenceRecord): array
    {
        if ($limit < 0) {
            return [];
        }

        // create query
        $query = $this -> postLinkRepository -> createQuery();

        // add restrictions
        $constraints = [];

        if ($filter['collectionIdentifiers']) {
            $constraints[] = $query -> in('account', $filter['collectionIdentifiers']);
        }
        if ($filter['hashtags']) {
            $constraints[] = $query -> in('post.hashtags.uid', $filter['hashtags']);
        }
        if ($filter['preselectedRecordIdentifiers']) {
            $constraints[] = $query -> logicalNot(
                $query -> in('uid', $filter['preselectedRecordIdentifiers'])
            );
        }

        if ($referenceRecord) {
            if ($filter['order'] == QueryInterface::ORDER_DESCENDING) {
                $constraints[] = $query -> logicalAnd(
                    $query -> lessThan('post.updated_time', $referenceRecord -> getUpdatedTime() -> format('Y-m-d H:i:s')),
                    $query -> logicalNot(
                        $query -> equals('post.id', $referenceRecord -> getId()),
                    )
                );
            } else {
                $constraints[] = $query -> logicalAnd(
                    $query -> greaterThan('post.updated_time', $referenceRecord -> getUpdatedTime() -> format('Y-m-d H:i:s')),
                    $query -> logicalNot(
                        $query -> equals('post.id', $referenceRecord -> getId()),
                    )
                );
            }
        }

        if ($constraints) {
            $query -> matching(
                $query -> logicalAnd(...$constraints)
            );
        }

        // set the limit
        if ($limit) {
            $query -> setLimit($limit);
        }
        // set ordering
        $query->setOrderings([
            'post.updated_time' => $filter['order'],
        ]);
        return $query -> execute() -> toArray();
    }
}
