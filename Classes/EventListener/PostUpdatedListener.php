<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\EventListener;

use Fixpunkt\FpSocial\Events\PostUpdatedEvent;
use Fixpunkt\FpSocial\Utilities\CacheUtility;

class PostUpdatedListener
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
     * @param PostUpdatedEvent $event
     */
    public function __invoke(PostUpdatedEvent $event): void
    {
        $this -> cacheUtility -> clearCacheForPost($event -> getPost());
    }
}
