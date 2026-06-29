<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\EventListener;

use Fixpunkt\FpFileprotector\Resource\Folder;
use Fixpunkt\FpFileprotector\Resource\ResourceStorage;
use TYPO3\CMS\Core\Imaging\Event\ModifyIconForResourcePropertiesEvent;
use TYPO3\CMS\Core\Resource\ResourceInterface;

class ModifyIconForResourcePropertiesListener
{
    public function __invoke(ModifyIconForResourcePropertiesEvent $event): void
    {
        $resource = $event->getResource();
        /** @var ResourceStorage $storage */
        $storage = $resource->getStorage();

        if ($storage->isProtected() && is_a($resource, Folder::class)) {
            $iconIdentifier = 'tx-fpfileprotector-folder-no-access';

            /** @var Folder $folder */
            $folder = $resource;
            if ($folder->isAccessible()) {
                if ($folder->isProtected()) {
                    $iconIdentifier = 'tx-fpfileprotector-folder-protected';
                } else {
                    $iconIdentifier = 'tx-fpfileprotector-folder-public';
                }
            }

            if ($folder->getOwnProtection()) {
                $event->setIconIdentifier($iconIdentifier);
            } else {
                $event->setOverlayIdentifier($iconIdentifier);
            }
        }
    }
}