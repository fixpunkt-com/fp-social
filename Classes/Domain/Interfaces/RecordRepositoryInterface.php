<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Interfaces;

interface RecordRepositoryInterface
{
    public function getRecordsByFilter(array $filter, int $limit, ?RecordInterface $referenceRecord): array;
    public function getRecordsByIdentifiers(array $identifiers): array;
    public function getAllRecordsForTca(): array;
    public function getCollectionsForTca(): array;
}
