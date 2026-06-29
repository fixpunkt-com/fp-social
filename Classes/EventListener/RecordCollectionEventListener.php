<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\EventListener;

use Fixpunkt\FpSocial\Domain\RecordCollection\RecordRepository;
use Fixpunkt\FpSocial\Events\RecordCollectionEvent;

class RecordCollectionEventListener
{
    /**
     * @param RecordCollectionEvent $event
     */
    public function __invoke(RecordCollectionEvent $event): void
    {
        $event -> addSource(
            'fpsocial',
            'tx_fpsocial_domain_model_account',
            'tx_fpsocial_domain_model_postlink',
            RecordRepository::class
        );
    }
}
