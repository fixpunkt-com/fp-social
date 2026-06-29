<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use Fixpunkt\FpFileprotector\Resource\ResourceStorage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileStorageRepository
{
    /**
     * Returns all file storages.
     *
     * @return array<ResourceStorage>
     */
    public function findAll(): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_storage')->createQueryBuilder();
        $query = $queryBuilder
            ->select('uid')->from('sys_file_storage')->executeQuery();

        $fileStorages = [];
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            foreach ($query->fetchAllAssociative() as $data) {
                $fileStorages[] = $resourceFactory->getStorageObject($data['uid']);
            }
        } catch (Exception) {
        }
        return $fileStorages;
    }

    /**
     * Returns a single resource storage.
     *
     * @param int $fileStorageUid
     * @return ResourceStorage
     */
    public function findByIdentifier(int $fileStorageUid): ResourceStorage
    {
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        return $resourceFactory->getStorageObject($fileStorageUid);
    }

    /**
     * Updates a file storage.
     *
     * @param ResourceStorage $fileStorage
     */
    public function update(ResourceStorage $fileStorage): void
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_storage')->createQueryBuilder();
        $queryBuilder
            ->update('sys_file_storage')
            ->set('protected', $fileStorage->isProtected())
            ->set('protected_by_default', $fileStorage->isProtectedByDefault() ?: 0)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileStorage->getUid())))
            ->executeStatement();
    }
}
