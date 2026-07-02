<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model\Account;

use Fixpunkt\FpSocial\Domain\Model\Account;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Instagram extends Account
{
    public const DESCRIPTION = 'Instagram';
    public const ICON = 'fa-instagram';
    public const PARTIAL_FOLDER = 'Fixpunkt_' . self::DESCRIPTION;

    /** @var string */
    protected string $inBusinessId = '';
    /** @var string */
    protected string $inMode = 'profile';
    /** @var string */
    protected string $inHashtag = '';
    /** @var string */
    protected string $inHashtagMode = 'recent_media';

    /**
     * @return string
     */
    public function getInBusinessId(): string
    {
        return $this -> inBusinessId;
    }
    /**
     * @param string $inBusinessId
     */
    public function setInBusinessId(string $inBusinessId): void
    {
        $this -> inBusinessId = $inBusinessId;
    }

    /**
     * @return string
     */
    public function getInMode(): string
    {
        return $this -> inMode;
    }
    /**
     * @param string $inMode
     */
    public function setInMode(string $inMode): void
    {
        $this -> inMode = $inMode;
    }

    /**
     * @return string
     */
    public function getInHashtag(): string
    {
        return $this -> inHashtag;
    }
    /**
     * @param string $inHashtag
     */
    public function setInHashtag(string $inHashtag): void
    {
        $this -> inHashtag = $inHashtag;
    }

    /**
     * @return string
     */
    public function getInHashtagMode(): string
    {
        return $this -> inHashtagMode;
    }
    /**
     * @param string $inHashtagMode
     */
    public function setInHashtagMode(string $inHashtagMode): void
    {
        $this -> inHashtagMode = $inHashtagMode;
    }

    /**
     * Gibt das unformatierte TCA Label zurück.
     * @return string
     */
    public static function getTCALabelAccount(int $uid): string
    {
        $data = parent::getRawData($uid);
        if ($data['in_mode'] == 'profile') {
            return self::DESCRIPTION . ': @' . $data['label'] . ' (' . $data['channel'] . ')';
        }
        $mode = LocalizationUtility::translate(
            'tx_fpsocial_domain_model_account.in_hashtag_mode.' . $data['in_hashtag_mode'],
            'fp_social'
        );
        return self::DESCRIPTION . ': #' . $data['in_hashtag'] . ' (' . $mode . ') durch @' . $data['label'] . ' (' . $data['channel'] . ')';

    }

    /**
     * Gibt die URL ohne Timestamp und Access Tokens zurück.
     * @param string $uri
     * @return string
     */
    public static function getPictureIdentifier(string $uri): string
    {
        $uriObject = \League\Uri\Uri::createFromString($uri);
        return $uriObject->getPath();
    }

    public function getChannelLink(): string
    {
        switch ($this -> getInMode()) {
            case 'profile':
                return "<a href='" . $this -> getChannelUri() . "' target='_blank'>@" . $this -> getLabel() . '</a>';
            case 'hashtag':
                return "<a href='" . $this -> getChannelUri() . "' target='_blank'>#" . $this -> getInHashtag() . '</a>';
        }

        return '';
    }
    public function getChannelUri(): string
    {
        switch ($this -> getInMode()) {
            case 'profile':
                return 'https://www.instagram.com/' . $this -> getLabel();
            case 'hashtag':
                return 'https://www.instagram.com/explore/tags/' . $this -> getInHashtag();
        }

        return '';
    }
}
