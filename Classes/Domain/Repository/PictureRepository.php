<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class PictureRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Findet alle Bilder eines Posts mit einem gewissen Identifier.
     * @param string $postId
     * @param string $identifier
     * @return QueryResultInterface
     */
    public function findByPostAndIdentifier(string $postId, string $identifier): QueryResultInterface
    {
        $query = $this -> createQuery();
        $query -> matching(
            $query -> logicalAnd(...[
                $query -> equals('post', $postId),
                $query -> equals('uriIdentifier', $identifier),
            ])
        );
        return $query -> execute();
    }

    /**
     * Sucht Accounts für die automatische Synchronisierung.
     * @param int $limit
     * @return QueryResultInterface
     */
    public function findForCommandTask(int $limit = 40): QueryResultInterface
    {
        // Wir suchen alle Pictures, die bereits eine FileReference haben.
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_picture');
        $statement = $queryBuilder
            -> select('picture.uid')
            -> from('tx_fpsocial_domain_model_picture', 'picture')
            -> join(
                'picture',
                'sys_file_reference',
                'reference',
                $queryBuilder->expr()->eq('reference.uid_foreign', $queryBuilder->quoteIdentifier('picture.uid'))
            )
            -> where(
                $queryBuilder->expr()->eq('reference.tablenames', $queryBuilder->createNamedParameter('tx_fpsocial_domain_model_picture'))
            )
            -> executeQuery();

        $uidsWithFileReference = [];
        foreach ($statement -> fetchAllAssociative() as $data) {
            $uidsWithFileReference[] = $data['uid'];
        }

        // Finde jetzt alle Pictures ohne Bild
        $query = $this -> createQuery();
        if (!empty($uidsWithFileReference)) {
            $query -> matching(
                $query -> logicalNot(
                    $query -> in('uid', $uidsWithFileReference)
                )
            );
        }
        $query -> setLimit($limit);
        $picturesWithoutFileReferences = $query -> execute();

        return $picturesWithoutFileReferences;
    }
}
