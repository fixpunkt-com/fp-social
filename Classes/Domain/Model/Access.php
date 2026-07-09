<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Domain\Model;

use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;
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
     * @param string $uri
     * @param array $parameters
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    private function connectWithFullUri(string $uri, array $parameters = []): SocialServerResponse
    {
        /** @var RequestFactory $requestFactory */
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        try {
            $response = $requestFactory ->request($uri, 'POST', [
                'form_params' => array_merge($parameters, [
                    'version' => self::version,
                    'auth' => [
                        'accesstoken' => $this -> getFpAccessToken(),
                        'username' => $this -> getFpUsername(),
                    ],
                ]),
            ]);
        } catch (ClientException $e) {
            $response = $e -> getResponse();
            $body = (string)$response -> getBody();

            /** @var SocialServerErrorResponse $response */
            $response = SocialServerResponse::fromJson($body);
            throw new \Exception(
                $response -> getMessage(),
                $response -> getCode()
            );
        } catch (ConnectException $e) {
            throw new \Exception('Die Adresse des Social Server konnte nicht aufgelöst werden. Überprüfe die API-Url in den Extension-Einstellungen.');
        } catch (RequestException $e) {
            throw new \Exception('Das angegebene Protokoll wird nicht unterstützt. Überprüfe die API-Url in den Extension-Einstellungen.');
        }

        // Antwort verarbeiten
        $body = (string)$response -> getBody();
        return SocialServerResponse::fromJson($body);
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
