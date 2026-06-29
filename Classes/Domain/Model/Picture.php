<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Picture extends AbstractEntity
{
    /** @var Post */
    protected ?Post $post = null;
    protected ?FileReference $filereference = null;
    /** @var string */
    protected string $uri = '';
    /** @var string */
    protected string $uriIdentifier = '';

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this -> post;
    }
    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this -> post = $post;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }
    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
        $this -> setUriIdentifier();
    }

    public function getFilereference(): ?FileReference
    {
        return $this -> filereference;
    }
    public function setFileReference(?FileReference $fileReference): void
    {
        $this -> filereference = $fileReference;
    }

    /**
     * @return string
     */
    public function getUriIdentifier(): string
    {
        return $this -> uriIdentifier;
    }
    private function setUriIdentifier(): void
    {
        /** @var class-string<Account> $networkClass */
        $networkClass = $this -> getPost() -> getNetworkClass();
        $this -> uriIdentifier = $networkClass::getPictureIdentifier($this -> getUri());
    }

    /**
     * Gibt den Alternativtext für dieses Bild zurück.
     * @return string
     */
    public function getAlternativeText(): string
    {
        if ($this -> getFilereference()) {
            $imageAlternative = $this -> getFilereference() -> getOriginalResource() -> getAlternative();
            if ($imageAlternative) {
                return $imageAlternative;
            }
        }
        return 'Beitragsbild zum Post ' . $this -> getPost() -> getUid();
    }
}
