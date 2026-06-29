<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Mention extends AbstractEntity
{
    /** @var string  */
    protected string $displayName = '';
    /** @var string  */
    protected string $systemName = '';

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }
    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getSystemName(): string
    {
        return $this->systemName;
    }
    /**
     * @param string $systemName
     */
    public function setSystemName(string $systemName): void
    {
        $this->systemName = $systemName;
    }
}
