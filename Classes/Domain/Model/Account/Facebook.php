<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model\Account;

use Fixpunkt\FpSocial\Domain\Model\Account;
use League\Uri\Components\Query;
use League\Uri\Uri;

class Facebook extends Account
{
    public const DESCRIPTION = 'Facebook';
    public const ICON = 'fa-facebook-f';
    public const PARTIAL_FOLDER = 'Fixpunkt_' . self::DESCRIPTION;

    /**
     * Gibt die URL ohne Timestamp und Access Tokens zurück.
     * @param string $uri
     * @return string
     */
    public static function getPictureIdentifier(string $uri): string
    {
        if (str_contains($uri, 'safe_image.php')) {
            $uriObject = Uri::createFromString($uri);
            $query = Query::createFromUri($uriObject);

            if ($query -> has('url')) {
                return $query -> get('url');
            }
            $parts = explode('&_nc_eui2', $uri);
            $urlWithoutParameters = array_shift($parts);
            $parts = explode('safe_image.php', $urlWithoutParameters);
            $urlWithoutHostAndParameters = array_pop($parts);
            $identifier = 'safe_image.php' . $urlWithoutHostAndParameters;

        } else {
            $parts = explode('?', $uri);
            $urlWithoutParameters = array_shift($parts);
            $parts = explode('/v/', $urlWithoutParameters);
            $urlWithoutHostAndParameters = array_pop($parts);
            $identifier = '/v/' . $urlWithoutHostAndParameters;
        }
        return $identifier;
    }

    /**
     * Gibt das unformatierte TCA Label zurück.
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
        return 'https://www.facebook.com/' . $this -> getChannel();
    }
}
