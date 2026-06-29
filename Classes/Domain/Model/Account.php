<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class Account extends AbstractEntity
{
    /** @var bool */
    protected bool $approve = true;
    /** @var string  */
    protected string $synchronizationError = '';
    /** @var string  */
    protected string $synchronizationInterval = '';
    /** @var string  */
    protected string $synchronizationIntervalString = '';
    /** @var Access|null */
    protected Access|null $access = null;
    /** @var string */
    protected string $label = '';
    /** @var string */
    protected string $channel = '';
    /** @var bool */
    protected bool $synchronize = true;
    /** @var \DateTime|null */
    protected ?\DateTime $lastSuccessfulSynchronization = null;
    /** @var string  */
    protected string $network = '';

    /**
     * @return bool
     */
    public function getApprove(): bool
    {
        return $this -> approve;
    }
    /**
     * @param bool $approve
     */
    public function setApprove(bool $approve): void
    {
        $this->approve = $approve;
    }

    /**
     * @return bool
     */
    public function getSynchronize(): bool
    {
        return $this -> synchronize;
    }
    public function setSynchronize(bool $synchronize): void
    {
        $this -> synchronize = $synchronize;
    }

    /**
     * @var \DateTime
     */
    protected $lastSynchronization;
    /**
     * @return \DateTime|null
     */
    public function getLastSynchronization(): ?\DateTime
    {
        return $this->lastSynchronization;
    }
    /**
     * @param \DateTime $lastSynchronization
     */
    public function setLastSynchronization(\DateTime $lastSynchronization): void
    {
        $this->lastSynchronization = $lastSynchronization;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastSuccessfulSynchronization(): ?\DateTime
    {
        return $this->lastSuccessfulSynchronization;
    }
    /**
     * @param \DateTime $lastSuccessfulSynchronization
     */
    public function setLastSuccessfulSynchronization(\DateTime $lastSuccessfulSynchronization): void
    {
        $this->lastSuccessfulSynchronization = $lastSuccessfulSynchronization;
        $this -> setLastSynchronization($lastSuccessfulSynchronization);
        $this -> setSynchronizationError('');
    }
    /**
     * Gibt zurück ob die letzte Synchronisation erfolgreich war.
     * @return bool
     */
    public function getLastSynchronizationWasSuccessful(): bool
    {
        return $this -> getLastSuccessfulSynchronization() && $this -> getLastSynchronization() && $this -> getLastSuccessfulSynchronization() -> format('U') == $this -> getLastSynchronization() -> format('U');
    }

    /**
     * @param string $synchronizationError
     */
    public function setSynchronizationError(string $synchronizationError): void
    {
        $this -> synchronizationError = $synchronizationError;
    }
    /**
     * @return string
     */
    public function getSynchronizationError(): string
    {
        return $this->synchronizationError;
    }

    /**
     * @param string $synchronizationInterval
     */
    public function setSynchronizationInterval(string $synchronizationInterval = ''): void
    {
        $this -> setSynchronize($synchronizationInterval != '-1');

        try {
            new \DateInterval($synchronizationInterval);
            $this -> synchronizationInterval = $synchronizationInterval;
        } catch (\Exception $e) {
            $this -> synchronizationInterval = '';
        }
    }
    public function getSynchronizationInterval(): ?\DateInterval
    {
        if (!$this -> synchronizationInterval) {
            return null;
        }

        try {
            return new \DateInterval($this -> synchronizationInterval);
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * @return string
     */
    public function getSynchronizationIntervalString(): string
    {
        if (!$this -> getSynchronize()) {
            return '-1';
        }
        if (!$this -> getSynchronizationInterval()) {
            return '';
        }

        $interval = $this -> getSynchronizationInterval();
        $string = 'P';
        if ($interval -> d) {
            $string .= $interval -> d . 'D';
        }
        if ($interval -> h || $interval -> i) {
            $string .= 'T';
            if ($interval -> h) {
                $string .= $interval -> h . 'H';
            }
            if ($interval -> i) {
                $string .= $interval -> i . 'M';
            }
        }

        return $interval != null ? $string : '';
    }

    /**
     * Gibt den frühesten Zeitpunkt der nächsten Synchronisation zurück.
     * @return \DateTime|null
     */
    public function getNextSynchronization(): ?\DateTime
    {
        $interval = $this -> getSynchronizationInterval();
        if ($interval) {
            $datetime = clone $this -> lastSynchronization;
            $datetime -> add($interval);
            return $datetime;
        }
        return $this -> getLastSynchronization();
    }

    /**
     * @return string
     */
    public function getNetwork(): string
    {
        return $this -> network;
    }

    /**
     * @return Access|null
     */
    public function getAccess(): ?Access
    {
        return $this -> access;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this -> label = $label;
    }
    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this -> label;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this -> channel = $channel;
    }
    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this -> channel;
    }
    abstract public function getChannelUri(): string;
    abstract public function getChannelLink(): string;

    // Bilderkennung
    abstract public static function getPictureIdentifier(string $uri): string;

    // TCA Labels
    abstract public static function getTCALabelAccount(int $uid): string;

    public static function getDescription(): string
    {
        return static::class::DESCRIPTION;
    }
    public static function getIcon(): string
    {
        return static::class::ICON;
    }

    public static function getPartialFolder(): string
    {
        return static::class::PARTIAL_FOLDER;
    }

    /**
     * @param string $uri
     * @return SocialServerResponse
     */
    public function readPostsFromUri(string $uri): SocialServerResponse
    {
        return $this -> getAccess() -> getPostsDataFromUri($uri);
    }

    public function getAmountOfPosts(): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_fpsocial_domain_model_postlink');
        $count = $queryBuilder
            ->count('uid')
            ->from('tx_fpsocial_domain_model_postlink')
            ->where(
                $queryBuilder->expr()->eq('account', $queryBuilder->createNamedParameter($this -> getUid(), Connection::PARAM_STR))
            )
            ->executeQuery()
            ->fetchOne();
        return $count;
    }

    /**
     * Returns the raw data of a given account uid.
     * @param int $uid
     * @return array
     */
    public static function getRawData(int $uid): array
    {
        return BackendUtility::getRecord('tx_fpsocial_domain_model_account', $uid);
    }
}
