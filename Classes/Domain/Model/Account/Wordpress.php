<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model\Account;

use Fixpunkt\FpSocial\Domain\Model\Account;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class Wordpress extends Account
{
    /** @var string  */
    public const DESCRIPTION = 'Wordpress';
    public const ICON = 'fa-wordpress';
    /** @var string  */
    public const PARTIAL_FOLDER = 'Fixpunkt_' . self::DESCRIPTION;

    /** @var string */
    protected string $wpMode = 'posts';
    /** @var string */
    protected string $wpTag = '';
    /** @var string */
    protected string $wpAuthor = '';
    /** @var string  */
    protected string $wpUrl = '';

    /**
     * @return string
     */
    public function getWpMode(): string
    {
        return $this -> wpMode;
    }
    /**
     * @param string $wpMode
     */
    public function setWpMode(string $wpMode): void
    {
        $this -> wpMode = $wpMode;
    }

    /**
     * @return string
     */
    public function getWpTag(): string
    {
        return $this -> wpTag;
    }
    /**
     * @param string $wpTag
     */
    public function setWpTag(string $wpTag): void
    {
        $this -> wpTag = $wpTag;
    }

    /**
     * @return string
     */
    public function getWpAuthor(): string
    {
        return $this -> wpAuthor;
    }
    /**
     * @param string $wpAuthor
     */
    public function setWpAuthor(string $wpAuthor): void
    {
        $this->wpAuthor = $wpAuthor;
    }

    /**
     * @return string
     */
    public function getWpUrl(): string
    {
        return $this->wpUrl;
    }

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
        $accessData = BackendUtility::getRecord('tx_fpsocial_domain_model_access', $data['access']);

        if ($accessData) {
            $return = self::DESCRIPTION . ': ' . $data['wp_url'];
            if ($data['wp_mode'] == 'tag') {
                return $return . ' - Tag: ' . $data['wp_tag'];
            }
            if ($data['wp_mode'] == 'author') {
                return $return . ' - Autor:in: ' . $data['wp_author'];
            }
            return $return;
        }
        return 'Die Zugriffsklasse ist nicht angegeben.';

    }

    public function getChannelLink(): string
    {
        return "<a href='" . $this -> getChannelUri() . "' target='_blank'>@" . $this -> getChannel() . '</a>';
    }
    public function getChannelUri(): string
    {
        return $this -> getWpUrl();
    }
}
