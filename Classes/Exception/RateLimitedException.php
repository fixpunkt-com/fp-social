<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Exception;

use Fixpunkt\FpSocialBridge\v2\Response\SocialServerRateLimitResponse;

/**
 * Der Social Server hat die Anfrage wegen eines erreichten Anfragelimits abgewiesen.
 *
 * Bewusst eine eigene Exception und kein gewoehnlicher Fehler: der Zugriff war nicht falsch,
 * nur zu frueh. Die Aufrufer koennen damit kurz warten oder das Konto auf den naechsten Lauf
 * verschieben, statt die Synchronisation als fehlgeschlagen zu verbuchen.
 */
class RateLimitedException extends \Exception
{
    public function __construct(
        string $message,
        int $code,
        private readonly \DateTimeImmutable $retryAfter,
        private readonly int $retryAfterSeconds,
        private readonly string $scope = ''
    ) {
        parent::__construct($message, $code);
    }

    /**
     * Baut die Exception aus der Antwort des Social Servers.
     * @param SocialServerRateLimitResponse $response
     * @param int $retryAfterFallback Sekunden aus dem Retry-After-Header, falls die Antwort keine nennt.
     * @return self
     */
    public static function fromResponse(
        SocialServerRateLimitResponse $response,
        int $retryAfterFallback = 0
    ): self {
        $seconds = $response -> getRetryAfterSeconds() ?: $retryAfterFallback;

        // Ohne belastbaren Zeitpunkt aus der Antwort selbst rechnen, damit getRetryAfter()
        // niemals in der Vergangenheit liegt.
        $retryAfter = $response -> getRetryAfterTimestamp() > 0
            ? $response -> getRetryAfter()
            : (new \DateTimeImmutable('@' . (time() + $seconds)))
                -> setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return new self(
            $response -> getMessage(),
            $response -> getCode(),
            $retryAfter,
            max(0, $seconds),
            $response -> getScope()
        );
    }

    /**
     * Zeitpunkt, ab dem der Dienst wieder nutzbar ist.
     * @return \DateTimeImmutable
     */
    public function getRetryAfter(): \DateTimeImmutable
    {
        return $this->retryAfter;
    }

    /**
     * Sekunden bis dahin.
     * @return int
     */
    public function getRetryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }

    /**
     * Welches Limit gegriffen hat: "profile" oder "general".
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
