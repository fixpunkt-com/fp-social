<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Repository\AccountRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostLinkRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostRepository;
use Fixpunkt\FpSocial\Events\AccountUpdatedEvent;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class AccountController extends AbstractController
{
    /**
     * @param AccountRepository $accountRepository
     * @param PostRepository $postRepository
     * @param PostLinkRepository $postLinkRepository
     * @param ModuleTemplateFactory $moduleTemplateFactory
     */
    public function __construct(
        protected readonly AccountRepository $accountRepository,
        protected readonly PostRepository $postRepository,
        protected readonly PostLinkRepository $postLinkRepository,
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        parent::__construct($moduleTemplateFactory);
    }

    /**
     * Lists all registered social media accounts.
     * @param string $query
     * @return ResponseInterface
     */
    public function listAction(string $query = ''): ResponseInterface
    {
        // Query einlesen
        $accounts = $this -> accountRepository -> findAccountsByQuery($query);
        $sortedAccounts = $this -> accountRepository -> sortAccoutsByNetwork($accounts);

        $view = $this -> moduleTemplateFactory -> create($this -> request);
        $view -> assignMultiple([
            'sortedAccounts' => $sortedAccounts,
            'query' => $_SESSION['fp_social']['query'],
        ]);
        return $view -> renderResponse('Account/List');
    }

    /**
     * Shows a single account and its posts.
     * @param Account $account
     * @param int $page
     * @return ResponseInterface
     */
    public function showAction(Account $account, int $page = 1): ResponseInterface
    {
        $posts = $this -> postLinkRepository -> findByAccount($account);
        $paginator = new QueryResultPaginator($posts, $page, 10);
        $pagination = new SimplePagination($paginator);

        $this -> settings = array_merge($this -> settings, [
            'paginationSettings' => [
                'controller' => 'Account',
                'action' => 'show',
                'page' => $page,
            ],
        ]);

        $view = $this -> moduleTemplateFactory -> create($this -> request);
        $view -> assignMultiple([
            'account' => $account,
            'posts' => $paginator -> getPaginatedItems(),
            'pagination' => $pagination,
            'page' => $page,
            'settings' => $this -> settings,
        ]);
        return $view -> renderResponse('Account/Show');
    }

    /**
     * Updates data of an account.
     * @param Account $account
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(Account $account): ResponseInterface
    {
        $this -> accountRepository -> update($account);
        GeneralUtility::makeInstance(AccountUpdatedEvent::class, $account);

        $this->addFlashMessage('Der Account wurde aktualisiert.');
        return $this -> redirect('list');
    }
}
