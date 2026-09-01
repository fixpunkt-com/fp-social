<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<\Fixpunkt\FpSocial\Domain\Model\Access>
 */
class AccessRepository extends Repository
{
    public function initializeObject()
    {
        // Zugangsdaten liegen in einem beliebigen Ordner - die Storage-Page darf nicht greifen.
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
}
