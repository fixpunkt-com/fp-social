<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model\Account;

use Fixpunkt\FpSocial\Domain\Model\Account;

class Bluesky extends Account
{
    /** @var string  */
    public const DESCRIPTION = 'Bluesky';
    public const ICON = 'fa-bluesky';
    /** @var string  */
    public const PARTIAL_FOLDER = 'Fixpunkt_' . self::DESCRIPTION;

    /****************************************************************
     * Abstract Class Methods
     ****************************************************************/
    /**
     * Gibt die URL ohne Timestamp und Access Tokens zurück.
     * @param string $uri
     * @return string
     */
    public static function getPictureIdentifier(string $uri): string
    {
        return $uri;
    }

    /**
     * Gibt das unformatierte TCA Label zurück.
     * @return string
     */
    public static function getTCALabelAccount(int $uid): string
    {
        $data = parent::getRawData($uid);
        return self::DESCRIPTION . ': ' . $data['channel'];
    }

    public function getChannelLink(): string
    {
        return "<a href='" . $this -> getChannelUri() . "' target='_blank'>@" . $this -> getLabel() . '</a>';
    }
    public function getChannelUri(): string
    {
        return 'https://bsky.app/profile/' . $this -> getChannel();
    }
}
