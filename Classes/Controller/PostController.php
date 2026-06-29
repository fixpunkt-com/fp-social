<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use Fixpunkt\FpSocial\Domain\Model\Post;
use Fixpunkt\FpSocial\Domain\Model\PostLink;
use Fixpunkt\FpSocial\Domain\Repository\AccountRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostRepository;
use Fixpunkt\FpSocial\Events\PostUpdatedEvent;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class PostController extends AbstractController
{
    /**
     * @param PostRepository $postRepository
     * @param AccountRepository $accountRepository
     * @param ModuleTemplateFactory $moduleTemplateFactory
     */
    public function __construct(
        protected readonly PostRepository $postRepository,
        protected readonly AccountRepository $accountRepository,
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        parent::__construct($moduleTemplateFactory);
    }

    /**
     * Searches posts.
     * @param int $page
     * @param array $search
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function searchAction(int $page = 1, array $search = []): ResponseInterface
    {
        $posts = $this -> postRepository -> search($search);
        $paginator = new ArrayPaginator($posts, $page, 10);
        $pagination = new SimplePagination($paginator);

        $this -> settings = array_merge($this -> settings, [
            'paginationSettings' => [
                'controller' => 'Post',
                'action' => 'search',
                'page' => $page,
            ],
        ]);

        $view = $this -> moduleTemplateFactory -> create($this -> request);
        $view -> assignMultiple([
            'search' => $search,
            'accounts' => $this -> accountRepository -> findAll(),
            'posts' => $paginator -> getPaginatedItems(),
            'pagination' => $pagination,
            'settings' => $this -> settings,
        ]);
        return $view -> renderResponse('Post/Search');
    }

    /**
     * Updates a single post.
     * @param Post $post
     * @param PostLink $postLink
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(Post $post, PostLink $postLink): void
    {
        $this -> postRepository -> update($post);
        GeneralUtility::makeInstance(PostUpdatedEvent::class, $post);

        $this -> addFlashMessage('Der Post wurde aktualisiert.');
        $this -> redirect('show', 'Account', null, ['account' => $postLink -> getAccount()]);
    }
}
