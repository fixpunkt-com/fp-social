<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Events;

use Fixpunkt\FpSocial\Domain\Interfaces\RecordInterface;
use Fixpunkt\FpSocial\Domain\Interfaces\RecordRepositoryInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class RecordCollectionEvent
{
    /** @var array  */
    protected array $sources = [];

    public function __construct()
    {
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $eventDispatcher -> dispatch($this);
    }

    /**
     * @param string $identifier
     */
    public function addSource(string $identifier, string $collectionIdentifier, string $recordIdentifier, string $class): void
    {
        if (class_exists($class)) {
            $this->sources[$identifier] = [
                'repository' => $class,
                'collectionIdentifier' => $collectionIdentifier,
                'recordIdentifier' => $recordIdentifier,
            ];
        }
    }

    /**
     * @return array
     */
    public function getSources(): array
    {
        return $this -> sources;
    }

    /**
     * Returns all collections from all registered sources as a tca array.
     */
    public function getCollectionsForTca(&$params): void
    {
        $items = [];
        foreach ($this -> getSources() as $identifier => $data) {
            /** @var RecordRepositoryInterface $repository */
            $repository = GeneralUtility::makeInstance($data['repository']);
            $items = array_merge(
                $items,
                $repository -> getCollectionsForTca()
            );
        }
        $params['items'] = $items;
    }

    public function getAllRecordsForTca(&$params): void
    {
        $items = [];
        foreach ($this -> getSources() as $identifier => $data) {
            /** @var RecordRepositoryInterface $repository */
            $repository = GeneralUtility::makeInstance($data['repository']);
            $items = array_merge(
                $items,
                $repository -> getAllRecordsForTca()
            );
        }
        $params['items'] = $items;
    }

    /**
     * Finds postlink by given uids.
     * @param array $recordIdentifiers
     * @throws InvalidQueryException
     */
    public function findByIdentifiers(array $recordIdentifiers): array
    {
        $records = [];

        foreach ($this -> getSources() as $identifier => $data) {
            $specificRecordIdentifiers = $this -> getRecordUidsByTable($recordIdentifiers, $data['recordIdentifier']);
            if (!$specificRecordIdentifiers) {
                continue;
            }

            /** @var RecordRepositoryInterface $repository */
            $repository = GeneralUtility::makeInstance($data['repository']);
            $externalRecords = $repository -> getRecordsByIdentifiers($specificRecordIdentifiers);
            $records = array_merge($records, $externalRecords);
        }

        return $records;
    }

    public function findByIdentifier($identifier)
    {
        $records = $this -> findByIdentifiers([$identifier]);
        return $records[0] ?? null;
    }

    public function getRecordsByFilter(array $filter, int $limit = 0, array $referenceRecordIdentifiers = [], int $offset = 0): array
    {
        if (!$limit || $limit < 0) {
            return [];
        }

        // read config
        $order = $filter['order'] ?? QueryInterface::ORDER_DESCENDING;
        $globalFilter = [
            'collectionIdentifiers' => is_array($filter['collectionIdentifiers'] ?? null) ? $filter['collectionIdentifiers'] : [],
            'hashtags' => is_array($filter['hashtags'] ?? null) ? $filter['hashtags'] : [],
            'preselectedRecordIdentifiers' => is_array($filter['preselectedRecordIdentifiers'] ?? null) ? $filter['preselectedRecordIdentifiers'] : [],
            'order' => in_array($order, [QueryInterface::ORDER_DESCENDING, QueryInterface::ORDER_ASCENDING]) ? $order : QueryInterface::ORDER_DESCENDING,
        ];

        $allRecords = [];
        foreach ($this -> getSources() as $identifier => $data) {
            $specificCollectionIdentifiers = $this -> getRecordUidsByTable($globalFilter['collectionIdentifiers'], $data['collectionIdentifier']);
            if (!$specificCollectionIdentifiers) {
                continue;
            }

            /** @var RecordRepositoryInterface $repository */
            $repository = GeneralUtility::makeInstance($data['repository']);

            // adjust filter
            $filter = $globalFilter;
            $filter['preselectedRecordIdentifiers'] = $this -> getRecordUidsByTable($filter['preselectedRecordIdentifiers'], $data['recordIdentifier']);
            $filter['collectionIdentifiers'] = $specificCollectionIdentifiers;

            // set reference record
            $referenceRecordIdentifier = $referenceRecordIdentifiers[$data['recordIdentifier']] ?? null;
            $referenceRecord = $this -> findByIdentifier($referenceRecordIdentifier);

            $records = $repository -> getRecordsByFilter(
                $filter,
                $limit + $offset,
                $referenceRecord
            );
            $allRecords = array_merge(
                $allRecords,
                $records
            );
        }

        // sort all records by datetime
        usort($allRecords, function (RecordInterface $a, RecordInterface $b): int {
            return $a -> getUpdatedTime() < $b -> getUpdatedTime() ? 1 : -1;
        });

        // return them ($limit is guaranteed > 0 by the early return above)
        return array_slice($allRecords, $offset, $limit);
    }

    /**
     * extracts the postlink uids for a given tablename
     * @param array $recordIdentifiers
     * @param string $tablename
     * @return array
     */
    protected function getRecordUidsByTable(array $recordIdentifiers, string $tablename): array
    {
        $postlinks = [];
        foreach ($recordIdentifiers as $identifier) {
            if (!is_string($identifier)) {
                continue;
            }
            $parts = explode(':', $identifier);
            if (count($parts) == 2 && $parts[0] == $tablename) {
                $postlinks[] = $parts[1];
            }
        }
        return $postlinks;
    }
}
