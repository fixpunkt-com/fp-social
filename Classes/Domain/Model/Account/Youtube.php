<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model\Account;

use Fixpunkt\FpSocial\Domain\Model\Account;

class Youtube extends Account
{
    /** @var string  */
    public const DESCRIPTION = 'Youtube';
    public const ICON = 'fa-youtube';
    /** @var string  */
    public const PARTIAL_FOLDER = 'Fixpunkt_' . self::DESCRIPTION;

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
        return self::DESCRIPTION . ': @' . $data['channel'];
    }

    public function getChannelLink(): string
    {
        return "<a href='" . $this -> getChannelUri() . "' target='_blank'>" . $this -> getChannel() . '</a>';
    }
    public function getChannelUri(): string
    {
        return 'https://www.youtube.com/user/' . $this -> getChannel();
    }
}
