<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Domain\Repository;

use Doctrine\DBAL\ParameterType;
use Fixpunkt\FpFileprotector\Domain\Model\Protection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FolderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ProtectionRepository extends Repository
{
    /**
     * Finds protection for a specific folder.
     *
     * @param FolderInterface $folder
     * @return Protection|null
     */
    public function findOneByFolder(FolderInterface $folder): ?Protection
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->logicalAnd(
                $query->equals('storage', $folder->getStorage()->getUid()),
                $query->equals('folder', $folder->getIdentifier())
            )
        );
        $results = $query->execute();

        return $results->current() ?: null;
    }

    /**
     * Finds folder protection for a folder or one of its parent folders.
     *
     * @param FolderInterface $folder
     * @param bool $recursive
     * @return Protection|null
     */
    public function getProtection(FolderInterface $folder, bool $recursive = true): ?Protection
    {
        $protection = $this->findOneByFolder($folder);
        if (!$protection && $recursive && $folder->hasParentFolder()) {
            return $this->getProtection($folder->getParentFolder());
        }
        return $protection;
    }

    /**
     * Static function to get protection data for middleware.
     * Injecting the repository itself into the middleware caused errors in some instances.
     *
     * @param FolderInterface $folder
     * @return Protection|null
     */
    public static function getProtectionStatic(FolderInterface $folder): ?Protection
    {
        // find protection data from database
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpfileprotector_domain_model_protection');
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_fpfileprotector_domain_model_protection')
            ->where(
                $queryBuilder->expr()->eq('storage', $queryBuilder->createNamedParameter($folder->getStorage()->getUid(), ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('folder', $queryBuilder->createNamedParameter($folder->getIdentifier()))
            )
            ->executeQuery();

        // Convert data to object and return
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $protections = $dataMapper->map(Protection::class, $statement->fetchAllAssociative());
        $protection = count($protections) ? $protections[0] : null;

        if (!$protection && $folder->hasParentFolder()) {
            return self::getProtectionStatic($folder->getParentFolder());
        }
        return $protection;
    }
}
