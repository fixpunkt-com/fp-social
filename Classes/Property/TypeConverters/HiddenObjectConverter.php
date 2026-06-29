<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Property\TypeConverters;

use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

// ToDo: TYPO3 das hier rausnehmen.
class HiddenObjectConverter extends PersistentObjectConverter
{
    /**
     * @var string
     */
    protected $targetType = 'Fixpunkt\\FpSocial\\Domain\\Model\\Post';
    /**
     * @var int
     */
    protected $priority = 2;

    /**
     * Fetch an object from persistence layer.
     *
     * @param mixed $identity
     * @param string $targetType
     * @return object
     * @throws InvalidSourceException
     * @throws TargetNotFoundException
     */
    protected function fetchObjectFromPersistence($identity, $targetType): object
    {
        if (ctype_digit((string)$identity)) {
            $query = $this->persistenceManager->createQueryForType($targetType);
            $query->getQuerySettings()->setIgnoreEnableFields(true);
            $query -> matching(
                $query->equals('uid', $identity)
            );

            $object = $query->execute()->getFirst();
        } else {
            throw new InvalidSourceException('The identity property "' . $identity . '" is no UID.', 1297931020);
        }
        if ($object === null) {
            throw new TargetNotFoundException('Object with identity "' . print_r($identity, true) . '" not found.', 1297933823);
        }
        return $object;
    }
}
