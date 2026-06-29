<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Model\Post;
use Fixpunkt\FpSocial\Domain\Repository\AccountRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostRepository;
use Fixpunkt\FpSocial\Utilities\SynchronizationUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class SynchronizationController extends AbstractController
{
    /**
     * AccountController constructor.
     * @param AccountRepository $accountRepository
     * @param PostRepository $postRepository
     * @param ModuleTemplateFactory $moduleTemplateFactory
     */
    public function __construct(
        protected readonly AccountRepository $accountRepository,
        protected readonly PostRepository $postRepository,
        protected readonly SynchronizationUtility $synchronizationUtility,
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        parent::__construct($moduleTemplateFactory);
    }

    /**
     * Listet alle Social Media Accounts auf.
     * @param string $query
     * @return ResponseInterface
     */
    public function listAction(string $query = ''): ResponseInterface
    {
        // Query einlesen
        $accounts = $this -> accountRepository -> findAll();
        $sortedAccounts = $this -> accountRepository -> sortAccoutsByNetwork($accounts);

        $view = $this -> moduleTemplateFactory -> create($this -> request);
        $view -> assignMultiple([
            'sortedAccounts' => $sortedAccounts,
        ]);
        return $view -> renderResponse('Synchronization/List');
    }

    /**
     * Updates if the account should be automatically synchronized and new posts should be available immediately or not.
     * @param Account $account
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(Account $account): ResponseInterface
    {
        $this -> accountRepository -> update($account);
        $this->addFlashMessage('Der Account wurde aktualisiert.');
        return $this -> redirect('list');
    }

    // Synchronisierungs Operationen

    /**
     * Synchronizes the current or all posts of an account.
     * @param Account $account
     * @param bool $deep
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function accountAction(Account $account, bool $deep = false): ResponseInterface
    {
        if ($deep) {
            $this -> view -> assignMultiple([
                'account' => $account,
            ]);
            return $this -> htmlResponse();
        }
        try {
            $amount = $this -> synchronizationUtility -> account($account);
            if ($amount) {
                $this->addFlashMessage($amount . ' Posts dieses Accounts wurden neu eingelesen oder aktualisiert.');
            } else {
                $this->addFlashMessage(
                    'Es wurden keine Posts eingelesen oder aktualisiert.',
                    '',
                    ContextualFeedbackSeverity::WARNING
                );
            }
        } catch (\Exception $e) {
            $this->addFlashMessage(
                $e->getCode() . ': ' . $e->getMessage(),
                'Es ist ein Fehler aufgetreten:',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this -> redirect('list', 'Account');

    }

    /**
     * Synchronizes a single post.
     * @param Post $post
     * @param Account $account
     * @param int $page
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function postAction(Post $post, Account $account, int $page = 1): ResponseInterface
    {
        try {
            $synchronized = $this -> synchronizationUtility -> post($account, $post);
            if ($synchronized) {
                $this -> addFlashMessage('Der Post wurde aktualisiert.');
            } else {
                $this->addFlashMessage(
                    "Der Post wurde im Urpsrungsnetzwerk gelöscht oder auf 'nicht öffentlich' gestellt. Der Post wurde deaktiviert.",
                    'Der Post konnte nicht eingelesen werden:',
                    ContextualFeedbackSeverity::WARNING
                );
            }
        } catch (\Exception $e) {
            $this->addFlashMessage(
                $e->getCode() . ': ' . $e->getMessage(),
                'Der Post konnte nicht eingelesen werden:',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this -> redirect('show', 'Account', null, [
            'account' => $account,
            'page' => $page,
        ]);
    }
}
