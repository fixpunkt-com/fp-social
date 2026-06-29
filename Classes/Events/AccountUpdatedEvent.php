<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Events;

use Fixpunkt\FpSocial\Domain\Model\Account;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AccountUpdatedEvent
{
    /** @var Account  */
    protected Account $account;

    /**
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this -> account = $account;

        /** @var EventDispatcher $dispatcher */
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $dispatcher -> dispatch($this);
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }
}
