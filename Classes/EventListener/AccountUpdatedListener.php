<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\EventListener;

use Fixpunkt\FpSocial\Events\AccountUpdatedEvent;
use Fixpunkt\FpSocial\Utilities\CacheUtility;

class AccountUpdatedListener
{
    /** @var CacheUtility  */
    protected CacheUtility $cacheUtility;

    /**
     * @param CacheUtility $cacheUtility
     */
    public function __construct(CacheUtility $cacheUtility)
    {
        $this -> cacheUtility = $cacheUtility;
    }

    /**
     * @param AccountUpdatedEvent $event
     */
    public function __invoke(AccountUpdatedEvent $event): void
    {
        $this -> cacheUtility -> clearCacheForAccount($event -> getAccount());
    }
}
