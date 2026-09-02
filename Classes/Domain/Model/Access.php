<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use Fixpunkt\FpSocial\Exception\RateLimitedException;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerRateLimitResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerUnrecognizedResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerVersionMismatchResponse;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Access extends AbstractEntity
{
    /** @var int  */
    private const version = 2;
    /** Wie oft eine wegen des Anfragelimits abgewiesene Anfrage wiederholt wird. */
    private const rateLimitRetries = 1;
    /** Vorgabe fuer rateLimitWaitThreshold, falls die Extension nicht konfiguriert ist. */
    private const rateLimitWaitThresholdDefault = 10;
    /** @var string */
    protected string $fpUsername = '';
    /** @var string */
    protected string $fpAccessToken = '';

    /**
     * @return string
     */
    public function getFpUsername(): string
    {
        return $this->fpUsername;
    }
    /**
     * @return string
     */
    public function getFpAccessToken(): string
    {
        return $this->fpAccessToken;
    }

    /**
     * Returns data of a single post as array.
     * @param Post $post
     * @param Account $account
     */
    public function getPostData(Account $account, Post $post): SocialServerResponse
    {
        switch (get_class($account)) {
            case Account\Facebook::class:
                return $this -> connect('networks/facebook/post', [
                    'postId' => $post -> getId(),
                ]);
            case Account\Instagram::class:
                return $this -> connect('networks/instagram/post', [
                    'postId' => $post -> getId(),
                ]);
            case Account\InstagramLogin::class:
                return $this -> connect('networks/instagramlogin/post', [
                    'postId' => $post -> getId(),
                ]);
            case Account\LinkedIn::class:
                return $this -> connect('networks/linkedin/post', [
                    'postId' => $post -> getId(),
                ]);
            case Account\Youtube::class:
                return $this -> connect('networks/youtube/video', [
                    'videoId' => $post -> getId(),
                ]);
            case Account\Wordpress::class:
                return $this -> connect('networks/wordpress/post', [
                    'postId' => $post -> getId(),
                    'baseUrl' => $account -> getWpUrl(),
                ]);
            case Account\Bluesky::class:
                return $this -> connect('networks/bluesky/post', [
                    'uri' => $post -> getId(),
                ]);
            default:
                throw new \Exception('This network is not supported by the FpSocialServer', 1652117377);
        }
    }

    /**
     * Liest die Daten der Posts eines Accounts ein und gibt sie unbearbeitet zurück.
     * @param string $position
     * @throws \Exception
     */
    public function getPostsData(Account $account, string $position = '0'): SocialServerResponse
    {
        switch (get_class($account)) {
            case Account\Facebook::class:
                return $this -> connect('networks/facebook/posts', [
                    'pageId' => $account -> getChannel(),
                ]);
            case Account\InstagramLogin::class:
                // Bewusst vor Instagram: dessen switch faellt am Ende ohne break durch, dieser
                // Zweig darf dabei nicht als Auffangbecken landen. Die Direktanmeldung kennt
                // keine Hashtag-Suche, deshalb nur der Profil-Endpunkt.
                return $this -> connect('networks/instagramlogin/posts', [
                    'pageId' => $account -> getChannel(),
                ]);
            case Account\Instagram::class:
                switch ($account->getInMode()) {
                    case 'hashtag':
                        return $this -> connect('networks/instagram/hashtag', [
                            'pageId' => $account -> getChannel(),
                            'mode' => $account -> getInHashtagMode(),
                            'hashtag' => $account -> getInHashtag(),
                        ]);
                    case 'profile':
                        return $this -> connect('networks/instagram/posts', [
                            'pageId' => $account -> getChannel(),
                        ]);
                }
                // no break
            case Account\LinkedIn::class:
                switch ($account->getLiMode()) {
                    case 'shares':
                        return $this -> connect('networks/linkedin/posts', [
                            'pageId' => $account -> getChannel(),
                        ]);
                    case 'ugc_posts':
                        return $this -> connect('networks/linkedin/ugcPosts', [
                            'pageId' => $account -> getChannel(),
                        ]);
                }
                // no break
            case Account\Wordpress::class:
                switch ($account->getWpMode()) {
                    case 'posts':
                        return $this -> connect('networks/wordpress/posts', [
                            'baseUrl' => $account -> getWpUrl(),
                        ]);
                    case 'tag':
                        return $this -> connect('networks/wordpress/postsWithTag', [
                            'baseUrl' => $account -> getWpUrl(),
                            'tag' => $account -> getWpTag(),
                        ]);
                    case 'author':
                        return $this -> connect('networks/wordpress/postsFromAuthor', [
                            'baseUrl' => $account -> getWpUrl(),
                            'author' => $account -> getWpAuthor(),
                        ]);
                }
                // no break
            case Account\Youtube::class:
                return $this -> connect('networks/youtube/videos', [
                    'channel' => $account -> getChannel(),
                ]);
            case Account\Bluesky::class:
                return $this -> connect('networks/bluesky/posts', [
                    'clientHandle' => $account -> getChannel(),
                ]);
            default:
                throw new \Exception('This network is not supported by the FpSocialServer', 1652117377);
        }
    }

    public function getPostsDataFromUri(string $uri): SocialServerResponse
    {
        // ToDo: Check if URI is correct.
        return $this -> connectWithFullUri($uri);
    }

    /**
     * Liest alle Zugaenge ein, die dieses Konto beim Social Server verbunden hat.
     * Die Antwort ist nach Netzwerk gruppiert (SocialServerAccountsResponse).
     * @throws \Exception
     */
    public function getAccountsData(): SocialServerResponse
    {
        return $this -> connect('accounts');
    }

    /**
     * Connects to the Social Server.
     * @param string $path
     * @param array $parameters
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    private function connect(string $path, array $parameters = []): SocialServerResponse
    {
        $uri = $this -> getServerUri() . $path;
        return $this -> connectWithFullUri($uri, $parameters);
    }

    /**
     * Connects to the Social Server.
     *
     * Weist der Server die Anfrage wegen eines erreichten Anfragelimits ab, wird eine kurze
     * Sperre hier ausgesessen und die Anfrage einmal wiederholt. Laengere Sperren gehen als
     * RateLimitedException nach oben - dort entscheidet sich, ob das Konto auf den naechsten
     * Lauf verschoben wird.
     *
     * @param string $uri
     * @param array $parameters
     * @param int $attempt
     * @throws RateLimitedException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    private function connectWithFullUri(string $uri, array $parameters = [], int $attempt = 1): SocialServerResponse
    {
        try {
            return $this -> performRequest($uri, $parameters);
        } catch (RateLimitedException $e) {
            $threshold = $this -> getRateLimitWaitThreshold();
            if ($threshold <= 0 || $attempt > self::rateLimitRetries || $e -> getRetryAfterSeconds() > $threshold) {
                throw $e;
            }

            // Eine Sekunde Zuschlag, weil der Server den Zeitpunkt auf ganze Sekunden abrundet.
            sleep(max(1, $e -> getRetryAfterSeconds() + 1));
            return $this -> connectWithFullUri($uri, $parameters, $attempt + 1);
        }
    }

    /**
     * Fuehrt die Anfrage aus und uebersetzt die Antwort in ein Response-Objekt.
     * @param string $uri
     * @param array $parameters
     * @throws RateLimitedException
     */
    private function performRequest(string $uri, array $parameters = []): SocialServerResponse
    {
        /** @var RequestFactory $requestFactory */
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        try {
            $response = $requestFactory ->request($uri, 'POST', [
                // Ohne Zeitgrenzen wartet Guzzle unbegrenzt; mit einem Wiederholungsversuch
                // liefe das leicht ueber das set_time_limit() des Kommandos hinaus.
                'connect_timeout' => 10,
                'timeout' => 30,
                'form_params' => array_merge($parameters, [
                    'version' => self::version,
                    'auth' => [
                        'accesstoken' => $this -> getFpAccessToken(),
                        'username' => $this -> getFpUsername(),
                    ],
                ]),
            ]);
        } catch (ClientException $e) {
            $errorResponse = $e -> getResponse();
            $body = (string)$errorResponse -> getBody();

            $socialServerResponse = SocialServerResponse::fromJson($body);

            $this -> assertUsableResponse(
                $socialServerResponse,
                (int)$errorResponse -> getHeaderLine('Retry-After')
            );

            // Eine abgewiesene Anfrage ohne Fehlerantwort laesst sich nicht sinnvoll melden -
            // dann bleibt nur der Statuscode als Auskunft.
            if (!$socialServerResponse instanceof SocialServerErrorResponse) {
                throw new \Exception(
                    'Der Social Server hat die Anfrage mit HTTP ' . $errorResponse -> getStatusCode()
                        . ' abgewiesen, ohne einen Fehler zu melden.',
                    1788393600
                );
            }

            throw new \Exception(
                $socialServerResponse -> getMessage(),
                $socialServerResponse -> getCode()
            );
        } catch (ConnectException $e) {
            throw new \Exception('Die Adresse des Social Server konnte nicht aufgelöst werden. Überprüfe die API-Url in den Extension-Einstellungen.');
        } catch (RequestException $e) {
            throw new \Exception('Das angegebene Protokoll wird nicht unterstützt. Überprüfe die API-Url in den Extension-Einstellungen.');
        }

        // Antwort verarbeiten
        $body = (string)$response -> getBody();
        $socialServerResponse = SocialServerResponse::fromJson($body);

        // Auch hier, falls der Server eine Drosselung je mit HTTP 200 ausliefert.
        $this -> assertUsableResponse($socialServerResponse);

        return $socialServerResponse;
    }

    /**
     * Prueft die Antwort auf die Faelle, die nicht am Inhalt liegen, sondern daran, dass die
     * Antwort selbst nicht passt.
     *
     * Seit fp_social_bridge 1.5 liefert die Bridge eine erreichte Drosselung, eine unpassende
     * Protokollversion und einen unbekannten Antworttyp als Fehlerantwort statt als Exception.
     * Ohne diese Uebersetzung stuende im Backend nur die technische Meldung der Bridge, die dem
     * Redakteur nicht sagt, was zu tun ist.
     *
     * @param SocialServerResponse $socialServerResponse
     * @param int $retryAfterFallback Sekunden aus dem Retry-After-Header, falls die Antwort keine nennt.
     * @throws RateLimitedException
     */
    private function assertUsableResponse(
        SocialServerResponse $socialServerResponse,
        int $retryAfterFallback = 0
    ): void {
        // Die Drosselung zuerst pruefen: die drei Typen erben alle von
        // SocialServerErrorResponse und wuerden sonst als gewoehnlicher Fehler durchrutschen.
        if ($socialServerResponse instanceof SocialServerRateLimitResponse) {
            throw RateLimitedException::fromResponse($socialServerResponse, $retryAfterFallback);
        }

        if ($socialServerResponse instanceof SocialServerVersionMismatchResponse) {
            throw new \Exception(
                'Der Social Server hat mit Protokollversion '
                    . $socialServerResponse -> getReceivedVersion() . ' geantwortet, erwartet wird Version '
                    . $socialServerResponse -> getExpectedVersion()
                    . '. Der Social Server und die Extension fp_social_bridge passen nicht zusammen -'
                    . ' überprüfe, ob beide auf dem aktuellen Stand sind.',
                $socialServerResponse -> getCode()
            );
        }

        if ($socialServerResponse instanceof SocialServerUnrecognizedResponse) {
            throw new \Exception(
                'Die Antwort des Social Server konnte nicht gelesen werden'
                    . ($socialServerResponse -> getReceivedType()
                        ? ' (Typ "' . $socialServerResponse -> getReceivedType() . '")'
                        : '')
                    . '. Kennt der Social Server einen Antworttyp, den die Extension'
                    . ' fp_social_bridge noch nicht kennt, hilft ein Update.',
                $socialServerResponse -> getCode()
            );
        }
    }

    /**
     * Bis zu wie vielen Sekunden eine Sperre des Social Servers ausgesessen wird. Alles
     * darueber verschiebt das Konto, 0 wartet grundsaetzlich nicht.
     * @return int
     */
    private function getRateLimitWaitThreshold(): int
    {
        try {
            $threshold = GeneralUtility::makeInstance(ExtensionConfiguration::class)
                -> get('fp_social', 'rateLimitWaitThreshold');
        } catch (\Throwable $e) {
            return self::rateLimitWaitThresholdDefault;
        }

        return ($threshold === '' || $threshold === null)
            ? self::rateLimitWaitThresholdDefault
            : (int)$threshold;
    }

    /**
     * @return string
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    private function getServerUri(): string
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        return $extensionConfiguration -> get('fp_social', 'apiUrl');
    }
}
