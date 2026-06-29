<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model\Account;

use Fixpunkt\FpSocial\Domain\Model\Account;
use League\Uri\Uri;

class LinkedIn extends Account
{
    /** @var string  */
    public const DESCRIPTION = 'LinkedIn';
    public const ICON = 'fa-linkedin';
    /** @var string  */
    public const PARTIAL_FOLDER = 'Fixpunkt_' . self::DESCRIPTION;
    /** @var string  */
    protected string $liMode = 'shares';

    /**
     * @return string
     */
    public function getLiMode(): string
    {
        return $this->liMode;
    }
    /**
     * @param string $liMode
     */
    public function setLiMode(string $liMode): void
    {
        $this->liMode = $liMode;
    }

    /**
     * Gibt das unformatierte TCA Label zurück.
     * @return string
     */
    public static function getTCALabelAccount(int $uid): string
    {
        $data = parent::getRawData($uid);

        $label = self::DESCRIPTION . ': @' . $data['label'] . ' (' . $data['channel'] . ')';
        switch ($data['li_mode']) {
            case 'shares':
                $label .= ' - Nur Shares';
                break;
            case 'ugc_posts':
                $label .= ' - Shares & UGC Posts';
                break;
        }
        return $label;
    }

    /**
     * Gibt die URL ohne Timestamp und Access Tokens zurück.
     * @param string $uri
     * @return string
     */
    public static function getPictureIdentifier(string $uri): string
    {
        $uriObject = Uri::createFromString($uri);
        return $uriObject -> getPath();
    }

    public function getChannelLink(): string
    {
        return '@' . $this -> getLabel() . '</a>';
    }
    public function getChannelUri(): string
    {
        return '';
    }
}
