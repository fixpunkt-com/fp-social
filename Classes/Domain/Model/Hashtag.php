<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Hashtag extends AbstractEntity
{
    /** @var string  */
    protected string $hashtag = '';

    /**
     * @return string
     */
    public function getHashtag(): string
    {
        return $this->hashtag;
    }
    /**
     * @param string $hashtag
     */
    public function setHashtag(string $hashtag): void
    {
        $this->hashtag = $hashtag;
    }
}
