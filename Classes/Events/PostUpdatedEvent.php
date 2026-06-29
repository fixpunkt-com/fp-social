<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Events;

use Fixpunkt\FpSocial\Domain\Model\Post;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PostUpdatedEvent
{
    /** @var Post  */
    protected Post $post;

    /**
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this -> post = $post;

        /** @var EventDispatcher $dispatcher */
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $dispatcher -> dispatch($this);
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this->post = $post;
    }
}
