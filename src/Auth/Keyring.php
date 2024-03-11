<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth\Auth;

use Crell\Serde\SerdeCommon;
use HSkrasek\LaravelZeroOAuth\Error;
use HSkrasek\LaravelZeroOAuth\Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

use function Crell\fp\afilter;
use function Crell\fp\keyedMap;
use function Crell\fp\pipe;

final class Keyring
{
    /**
     * @var array<string, Token>
     */
    private array $keyring;

    private array $keyringHashMap;

    public function __construct(
        private readonly AbstractProvider $provider,
        private readonly SerdeCommon $serde,
        private readonly string $path
    ) {
        $this->load($path);
    }

    public function get(string $key): ?Token
    {
        return $this->keyring[$key] ?? null;
    }

    public function add(string $name, Token $token): Token
    {
        $this->keyring[$name] = $token;
        $this->keyringHashMap[spl_object_hash($token)] = $name;

        return tap($token, fn (Token $token) => $this->saveToken($name, $token));
    }

    /**
     * @throws IdentityProviderException
     */
    public function refresh(Token $token): Token
    {
        if ($token->isValid()) {
            return $token;
        }

        return tap(Token::fromAccessToken(accessToken: $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->refreshToken,
        ])), fn (Token $newToken): Token => $this->add($this->nameFromToken($token), $newToken));
    }

    public function forceRefresh(Token $token): Token
    {
        return tap(Token::fromAccessToken(accessToken: $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->refreshToken,
        ])), fn (Token $newToken): Token => $this->add($this->nameFromToken($token), $newToken));
    }

    public function saveToken(string $name, Token $token): void
    {
        if (file_put_contents(
            filename: $this->path . "/$name.json",
            data: $this->serde->serialize($token, format: 'json'),
        ) === false) {
            // TODO: Replace with a custom exception
            throw new \RuntimeException('Unable to save access token.');
        }
    }

    private function nameFromToken(Token $token): string
    {
        return $this->keyringHashMap[spl_object_hash($token)];
    }

    private function load(string $path): void
    {
        $this->keyring = pipe(
            $path . '/*.json',
            glob(...),
            keyedMap(
                values: function (string $file): Error|Token {
                    try {
                        return Token::fromJson(file_get_contents($file));
                    } catch (\JsonException $e) {
                        return Error::fromThrowable($e);
                    }
                },
                keys: fn (string $file): string => pathinfo($file, PATHINFO_FILENAME),
            )(...),
            afilter(fn (Error|Token $token): bool => $token instanceof Token)(...),
        );

        $this->keyringHashMap = pipe(
            $this->keyring,
            keyedMap(
                values: fn (Token $token, string $name): string => $name,
                keys: fn (Token $token): string => spl_object_hash($token),
            )(...),
        );
    }
}
