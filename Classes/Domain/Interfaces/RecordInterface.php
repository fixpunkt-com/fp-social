<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Interfaces;

use Fixpunkt\FpSocial\Domain\Model\Account;

/**
 * Interface Record
 * Stellt sicher, dass der PostLink als Forwarding für den Post funktioniert und somit wenig angepasst werden muss.
 */
interface RecordInterface
{
    public function getId(): string;
    public function getNetwork(): string;
    public function getAccount(): Account;
    public function getUrl(): string;
    public function getUpdatedTime();
    public function getMessage(): string;
    public function getPicture(): string;
    public function getLink(): string;
    public function getHeadline(): string;
    public function getPictures();
    public function asJson(): array;
    public function getSelectedOrFirstPicture();
    public function getIdentifier(): string;
}
