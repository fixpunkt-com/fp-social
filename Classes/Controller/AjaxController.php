<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Repository\AccountRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostRepository;
use Fixpunkt\FpSocial\Events\AccountUpdatedEvent;
use Fixpunkt\FpSocialBridge\v2\Data\Post;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Backend-Ajax-Endpunkt für die seitenweise Tiefensynchronisierung eines Accounts.
 * Wird von Resources/Public/JavaScript/Backend/deepsynchronization.js aufgerufen.
 */
class AjaxController
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param AccountRepository $accountRepository
     * @param PostRepository $postRepository
     * @param PersistenceManager $persistenceManager
     */
    public function __construct(
        protected readonly ResponseFactoryInterface $responseFactory,
        protected readonly AccountRepository $accountRepository,
        protected readonly PostRepository $postRepository,
        protected readonly PersistenceManager $persistenceManager
    ) {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings -> setRespectStoragePage(false);
        $this -> accountRepository -> setDefaultQuerySettings($querySettings);
        $this -> postRepository -> setDefaultQuerySettings($querySettings);
    }

    /**
     * Liest eine Seite von Posts eines Accounts ein. Ohne "uri" wird die erste Seite gelesen,
     * mit "uri" die vom Social Server gemeldete Folgeseite.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function synchronizeAction(ServerRequestInterface $request): ResponseInterface
    {
        // Account einlesen
        $accountUid = (int)($request -> getQueryParams()['account'] ?? 0);
        $account = $this -> getAccount($accountUid);
        if (!$account) {
            return $this -> createResponse([
                'errorCode' => 1753747200,
                'errorMessage' => 'No account with uid ' . $accountUid . ' could be found!',
            ], 500);
        }

        $uri = (string)($request -> getQueryParams()['uri'] ?? '');

        try {
            // Posts einlesen
            $response = $uri
                ? $account -> readPostsFromUri($uri)
                : $account -> getAccess() -> getPostsData($account);

            if (is_a($response, SocialServerErrorResponse::class)) {
                throw new \Exception($response -> getMessage(), $response -> getCode());
            }
            if (!is_a($response, SocialServerPostsResponse::class)) {
                throw new \Exception(
                    'Response format not expected: ' . get_class($response) . '. Expected: ' . SocialServerPostsResponse::class,
                    1741293955
                );
            }

            // Posts speichern
            /** @var Post $post */
            foreach ($response -> getPosts() as $post) {
                $this -> postRepository -> addOrUpdate($account, $post);
            }

            $account -> setLastSuccessfulSynchronization(new \DateTime());
            $this -> accountRepository -> update($account);
            $this -> persistenceManager -> persistAll();

            // Frontend-Cache leeren, falls sich etwas geändert hat
            GeneralUtility::makeInstance(AccountUpdatedEvent::class, $account);
        } catch (\Throwable $e) {
            $account -> setSynchronizationError($e -> getMessage());
            $account -> setLastSynchronization(new \DateTime());
            $this -> accountRepository -> update($account);
            $this -> persistenceManager -> persistAll();

            return $this -> createResponse([
                'errorCode' => $e -> getCode(),
                'errorMessage' => 'Problem reading posts: ' . $e -> getMessage(),
            ], 500);
        }

        // Nächste Request ausgeben
        return $this -> createResponse($response -> toArray());
    }

    /**
     * Retreives an account by its uid.
     * @param int $accountUid
     * @return Account|null
     */
    private function getAccount(int $accountUid): ?Account
    {
        return $this -> accountRepository -> findByIdentifier($accountUid);
    }

    /**
     * Creates an response.
     * @param array $data
     * @param int $status
     * @return ResponseInterface
     */
    private function createResponse(array $data, int $status = 200): ResponseInterface
    {
        $response = $this -> responseFactory
            -> createResponse()
            -> withHeader('Content-Type', 'application/json; charset=utf-8')
            -> withStatus($status);
        $response -> getBody() -> write(json_encode($data));
        return $response;
    }
}
