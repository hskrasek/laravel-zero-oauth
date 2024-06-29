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

    /**
     * @var array<string, string>
     */
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
        if (! is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        if (file_put_contents(
            filename: $this->path."/$name.json",
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
            $path.'/*.json',
            glob(...),
            keyedMap(
                values: function (int $_, string $file): Error|Token {
                    try {
                        $json = file_get_contents($file);

                        if ($json === false) {
                            return Error::fromMessage('Unable to read access token.');
                        }

                        return $this->serde->deserialize(
                            $json,
                            from: 'json',
                            to: Token::class,
                        );
                    } catch (\JsonException $e) {
                        return Error::fromThrowable($e);
                    }
                },
                keys: fn (int $_, string $file): string => pathinfo($file, PATHINFO_FILENAME),
            )(...),
            afilter(fn (Error|Token $token): bool => $token instanceof Token)(...),
        );

        $this->keyringHashMap = pipe(
            $this->keyring,
            keyedMap(
                values: fn (string $name, Token $token): string => $name,
                keys: fn (string $name, Token $token): string => spl_object_hash($token),
            )(...),
        );
    }
}
