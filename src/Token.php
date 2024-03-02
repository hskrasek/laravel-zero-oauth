<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth;

use League\OAuth2\Client\Token\AccessTokenInterface;

final readonly class Token implements \JsonSerializable
{
    private function __construct(
        public string $accessToken,
        public string $refreshToken,
        public ?int $expires,
        public array $values = [],
    ) {
    }

    public static function fromAccessToken(AccessTokenInterface $accessToken): self
    {
        return new self(
            accessToken: $accessToken->getToken(),
            refreshToken: $accessToken->getRefreshToken(),
            expires: $accessToken->getExpires(),
            values: $accessToken->getValues(),
        );
    }

    public static function fromJson(string $json): self
    {
        try {
            /** @var array{access_token: string, refresh_token: string, expires: int, values: array} $data */
            $data = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Unable to decode access token.', previous: $e);
        }

        return new self(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'],
            expires: $data['expires'],
            values: $data['values'],
        );
    }

    public function isValid(): bool
    {
        return $this->expires < time();
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires' => $this->expires,
            'values' => $this->values,
        ];
    }
}
