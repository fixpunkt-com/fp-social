<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Userfuncs;

use Fixpunkt\FpSocial\Domain\Model\Account;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Labels
{
    /**
     * Gibt das TCA Label für einen Post zurück.
     * @param $parameters
     * @param $parentObject
     */
    public function post(&$parameters, $parentObject)
    {
        if (!($parameters['table'] ?? null) || !($parameters['row']['uid'] ?? null)) {
            return;
        }

        $postData = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        if ($postData) {
            // join the data to find network type
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_fpsocial_domain_model_postlink')->createQueryBuilder();
            $queryBuilder
                -> select('account.network')
                -> from('tx_fpsocial_domain_model_postlink', 'postlink')
                -> join(
                    'postlink',
                    'tx_fpsocial_domain_model_account',
                    'account',
                    (string)($queryBuilder -> expr() -> and(
                        $queryBuilder->expr()->eq('account.uid', $queryBuilder->quoteIdentifier('postlink.account')),
                        $queryBuilder->expr()->eq('postlink.post', $queryBuilder->createNamedParameter($postData['uid']))
                    ))
                )
                -> setMaxResults(1);
            $networkClass = $queryBuilder -> executeQuery() -> fetchOne();

            if ($networkClass) {
                $class = '\\' . $networkClass;
                $parameters['title'] = $class::DESCRIPTION . ': ' . $postData['id'];
            } else {
                $parameters['title'] = 'Post nicht gefunden';
            }
        }
    }

    /**
     * Gibt das TCA Label für einen Account zurück.
     * @param $parameters
     * @param $parentObject
     */
    public function account(&$parameters, $parentObject)
    {
        if (!($parameters['table'] ?? null) || !($parameters['row']['uid'] ?? null)) {
            return;
        }

        $accountData = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        if ($accountData && class_exists($accountData['network'])) {
            /** @var class-string<Account> $networkClass */
            $networkClass = $accountData['network'];
            $parameters['title'] = $networkClass::getTCALabelAccount($accountData['uid']);
        } else {
            $parameters['title'] = 'Keine Label Klasse gefunden!';
        }
    }
}
