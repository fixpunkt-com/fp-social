<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model\Account;

use Fixpunkt\FpSocial\Domain\Model\Account;

/**
 * Instagram ueber die Direktanmeldung, also ohne den Umweg ueber eine Facebook-Seite.
 *
 * Bewusst schlanker als Instagram: der Social Server bietet fuer dieses Netzwerk keine
 * Hashtag-Suche an (siehe InstagramLoginController dort), es gibt also nur den Profil-Modus
 * und damit auch keine in_mode-/in_hashtag-Felder.
 */
class InstagramLogin extends Account
{
    /** @var string  */
    public const DESCRIPTION = 'Instagram (Direktanmeldung)';
    public const ICON = 'fa-instagram';
    /**
     * Nicht aus DESCRIPTION abgeleitet wie bei den uebrigen Netzwerken: der Anzeigename
     * enthaelt Leerzeichen und Klammern und taugt damit nicht als Ordnername.
     * @var string
     */
    public const PARTIAL_FOLDER = 'Fixpunkt_InstagramLogin';

    /****************************************************************
     * Abstract Class Methods
     ****************************************************************/
    /**
     * Gibt die URL ohne Timestamp und Access Tokens zurueck.
     * @param string $uri
     * @return string
     */
    public static function getPictureIdentifier(string $uri): string
    {
        $uriObject = \League\Uri\Uri::createFromString($uri);
        return $uriObject -> getPath();
    }

    /**
     * Gibt das unformatierte TCA Label zurueck.
     * @return string
     */
    public static function getTCALabelAccount(int $uid): string
    {
        $data = parent::getRawData($uid);
        return self::DESCRIPTION . ': @' . $data['label'] . ' (' . $data['channel'] . ')';
    }

    public function getChannelLink(): string
    {
        return "<a href='" . $this -> getChannelUri() . "' target='_blank'>@" . $this -> getLabel() . '</a>';
    }
    public function getChannelUri(): string
    {
        return 'https://www.instagram.com/' . $this -> getLabel();
    }
}
