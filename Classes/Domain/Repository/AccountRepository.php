<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class AccountRepository extends Repository
{
    // Order by BE sorting
    protected $defaultOrderings = [
        'network' => QueryInterface::ORDER_ASCENDING,
    ];

    public function initializeObject()
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Sorts given accounts by their networks
     * @param $accounts
     * @return array
     */
    public function sortAccoutsByNetwork($accounts): array
    {
        $sorted = [];
        /** @var \Fixpunkt\FpSocial\Domain\Model\Account $account */
        foreach ($accounts as $account) {
            if (!array_key_exists($account -> getNetwork(), $sorted)) {
                $sorted[$account -> getNetwork()] = [];
            }
            $sorted[$account -> getNetwork()][] = $account;
        }
        return $sorted;
    }

    /**
     * Searches for accounts by a given or in session stored query
     * @param $queryList
     * @return QueryResultInterface
     */
    public function findAccountsByQuery($queryList): QueryResultInterface
    {
        // get query
        session_start();
        if ($queryList) {
            if ($queryList == 'none') {
                $_SESSION['fp_social']['query'] = '';
            } else {
                $_SESSION['fp_social']['query'] = $queryList;
            }
        } else {
            if (!isset($_SESSION['fp_social']['query'])) {
                $_SESSION['fp_social']['query'] = '';
            }
        }

        // if no query, then get all
        if (!$_SESSION['fp_social']['query']) {
            return $this->findAll();
        }

        // otherwise find by query
        $query = $this -> createQuery();

        // Argumente einlesen
        $argumentList = explode('&', $queryList);
        $arguments = [];
        foreach ($argumentList as $argumentPair) {
            $pair = explode('=', $argumentPair);
            $arguments[] = $query -> equals($pair[0], $pair[1]);
        }

        // Filtern
        $query -> matching(
            $query -> logicalAnd(...$arguments)
        );
        $result = $query -> execute();
        return $result;
    }
}
