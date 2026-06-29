<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Repository;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @method QueryResultInterface findByAccount(\Fixpunkt\FpSocial\Domain\Model\Account $account)
 */
class PostLinkRepository extends Repository
{
    // Order by BE sorting
    protected $defaultOrderings = [
        'post.updatedTime' => QueryInterface::ORDER_DESCENDING,
    ];
    public function initializeObject()
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        // pids ausschalten
        $querySettings->setRespectStoragePage(false);
        // hidden im Backend trotzdem anzeigen
        if (Environment::isCli() || ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setEnableFieldsToBeIgnored([
                'disabled', 'hidden',
            ]);
        }
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Findet Posts in mehreren Accounts welche vor dem Referenzpost gepostet wurden.
     * @param \Fixpunkt\FpSocial\Domain\Model\Post $referencePost
     * @param array $accounts
     * @param int $limit
     * @return QueryResultInterface
     */
    public function findPostsBefore(\Fixpunkt\FpSocial\Domain\Model\Post $referencePost, array $accounts, array $hashtags, int $limit = 1): QueryResultInterface
    {
        // Posts einlesen
        $query = $this -> createQuery();
        $constraints = [
            $query -> in('account', $accounts),
            $query -> lessThan('post.updated_time', $referencePost -> getUpdatedTime() -> format('Y-m-d H:i:s')),
            $query -> logicalNot(
                $query -> equals('post.uid', $referencePost -> getUid())
            ),
        ];
        if (count($hashtags)) {
            $constraints[] = $query -> in('post.hashtags.uid', $hashtags);
        }
        $query -> matching(
            $query -> logicalAnd(...$constraints)
        );
        $query->setOrderings([
            'post.updated_time' => QueryInterface::ORDER_DESCENDING,
        ]);
        $query -> setLimit($limit);

        $result = $query -> execute();
        return $result;
    }

    /**
     * Findet Posts in mehreren Accounts welche nach dem Referenzpost gepostet wurden.
     * @param \Fixpunkt\FpSocial\Domain\Model\Post $referencePost
     * @param array $accounts
     * @return QueryResultInterface
     */
    public function findPostsAfter(\Fixpunkt\FpSocial\Domain\Model\Post $referencePost, array $accounts, array $hashtags): QueryResultInterface
    {
        // Posts einlesen
        $query = $this -> createQuery();
        $constraints = [
            $query -> in('account', $accounts),
            $query -> greaterThan('post.updated_time', $referencePost -> getUpdatedTime() -> format('Y-m-d H:i:s')),
            $query -> logicalNot(
                $query -> equals('post.uid', $referencePost -> getUid())
            ),
        ];
        if (count($hashtags)) {
            $constraints[] = $query -> in('post.hashtags.uid', $hashtags);
        }

        $query -> matching(
            $query -> logicalAnd(...$constraints)
        );

        $query->setOrderings([
            'post.updated_time' => QueryInterface::ORDER_ASCENDING,
        ]);

        $result = $query -> execute();

        return $result;
    }

    public function findByAccountForFlexform(int $accoutUid = 0): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_fpsocial_domain_model_postlink')->createQueryBuilder();
        $statement = $queryBuilder
            ->select('postLinks.uid AS postLinkUid', 'posts.id as postId')
            ->from('tx_fpsocial_domain_model_postlink', 'postLinks')
            ->join(
                'postLinks',
                'tx_fpsocial_domain_model_post',
                'posts',
                $queryBuilder->expr()->eq('posts.uid', $queryBuilder->quoteIdentifier('postLinks.post'))
            );
        if ($accoutUid) {
            $statement ->where(
                $queryBuilder->expr()->eq('postLinks.account', $queryBuilder->createNamedParameter($accoutUid))
            );
        }

        $result = $statement -> executeQuery() -> fetchAllAssociative();
        return $result;
    }
}
