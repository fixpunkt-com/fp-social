<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use Fixpunkt\FpSocial\Domain\Model\Account;
use Fixpunkt\FpSocial\Domain\Repository\AccessRepository;
use Fixpunkt\FpSocial\Domain\Repository\AccountRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostRepository;
use Fixpunkt\FpSocial\Events\AccountUpdatedEvent;
// Der Name Account ist hier schon vom eigenen Model belegt.
use Fixpunkt\FpSocialBridge\v2\Data\Account as SocialServerAccount;
use Fixpunkt\FpSocialBridge\v2\Data\Channel;
use Fixpunkt\FpSocialBridge\v2\Data\Post;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerAccountsResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Backend-Ajax-Endpunkte des Managers.
 *
 * - synchronizeAction() für die seitenweise Tiefensynchronisierung eines Accounts,
 *   aufgerufen von Resources/Public/JavaScript/Backend/deepsynchronization.js.
 * - accountsAction() für die Kanalauswahl im TCA-Formular, aufgerufen von
 *   Resources/Public/JavaScript/Backend/AccountPicker.js.
 */
class AjaxController
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param AccountRepository $accountRepository
     * @param PostRepository $postRepository
     * @param PersistenceManager $persistenceManager
     * @param AccessRepository $accessRepository
     */
    public function __construct(
        protected readonly ResponseFactoryInterface $responseFactory,
        protected readonly AccountRepository $accountRepository,
        protected readonly PostRepository $postRepository,
        protected readonly PersistenceManager $persistenceManager,
        protected readonly AccessRepository $accessRepository
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
     * Liefert die beim Social Server verbundenen Zugaenge samt ihrer Kanaele.
     *
     * Uebergeben wird bewusst die Uid der Zugangsdaten und nicht die des Accounts: der
     * Account-Datensatz existiert beim Anlegen noch gar nicht, die Zugangsdaten schon.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function accountsAction(ServerRequestInterface $request): ResponseInterface
    {
        // Wer keine Accounts pflegen darf, braucht auch die Kanalliste nicht - die Antwort
        // entsteht aus fremden Zugangsdaten. Fuer Admins ist check() immer true.
        if (!$this -> getBackendUser() -> check('tables_modify', 'tx_fpsocial_domain_model_account')) {
            return $this -> createResponse([
                'errorCode' => 1756900001,
                'errorMessage' => 'Missing permission for table tx_fpsocial_domain_model_account.',
            ], 403);
        }

        $accessUid = (int)($request -> getQueryParams()['access'] ?? 0);
        $access = $accessUid > 0 ? $this -> accessRepository -> findByIdentifier($accessUid) : null;
        if (!$access) {
            return $this -> createResponse([
                'errorCode' => 1756900002,
                'errorMessage' => 'No access with uid ' . $accessUid . ' could be found!',
            ], 404);
        }

        try {
            $response = $access -> getAccountsData();

            if (is_a($response, SocialServerErrorResponse::class)) {
                throw new \Exception($response -> getMessage(), $response -> getCode());
            }
            if (!is_a($response, SocialServerAccountsResponse::class)) {
                throw new \Exception(
                    'Response format not expected: ' . get_class($response) . '. Expected: ' . SocialServerAccountsResponse::class,
                    1756900003
                );
            }
        } catch (\Throwable $e) {
            return $this -> createResponse([
                'errorCode' => $e -> getCode(),
                'errorMessage' => 'Problem reading accounts: ' . $e -> getMessage(),
            ], 500);
        }

        return $this -> createResponse(['accounts' => $this -> prepareAccounts($response)]);
    }

    /**
     * Uebersetzt die Antwort des Social Servers in die flache Struktur, die das
     * JavaScript erwartet.
     *
     * Bewusst nicht Accounts::toArray(): dort steckt in "expires" ein rohes DateTime, das
     * json_encode() als verschachteltes Objekt ausgeben wuerde. Ausserdem gehoert die
     * Zuordnung Netzwerk-Kurzform => Modellklasse nach PHP - nur hier sind die
     * Klassennamen bekannt, die zugleich die Item-Werte des TCA-Feldes "network" sind.
     * @param SocialServerAccountsResponse $response
     * @return array<int, array<string, mixed>>
     */
    private function prepareAccounts(SocialServerAccountsResponse $response): array
    {
        $accounts = [];

        /** @var SocialServerAccount $account */
        foreach ($response -> getAccounts() -> getAll() as $account) {
            $accountClass = Account::SOCIAL_SERVER_NETWORKS[$account -> getNetworkKey()] ?? null;
            if (!$accountClass) {
                // Ein Netzwerk, das diese Extension (noch) nicht kennt - lieber ueberspringen
                // als auf ein falsches Modell raten.
                continue;
            }

            $channels = [];
            /** @var Channel $channel */
            foreach ($account -> getChannels() as $channel) {
                $channels[] = [
                    'id' => $channel -> getId(),
                    'name' => $channel -> getName(),
                ];
            }

            $accounts[] = [
                'networkKey' => $account -> getNetworkKey(),
                'networkName' => $account -> getNetworkName(),
                'accountClass' => $accountClass,
                'accountLabel' => $accountClass::DESCRIPTION,
                'displayName' => $account -> getDisplayName(),
                'username' => $account -> getUsername(),
                'expired' => $account -> getExpired(),
                'expires' => $account -> getExpires()?-> format('Y-m-d'),
                'channels' => $channels,
            ];
        }

        return $accounts;
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    private function getBackendUser(): \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
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
