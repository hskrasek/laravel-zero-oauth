<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth\Http\Middleware;

use Crell\Serde\SerdeCommon;
use HSkrasek\LaravelZeroOAuth\Error;
use HSkrasek\LaravelZeroOAuth\Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\RequestInterface;

use function Crell\fp\pipe;

final readonly class RefreshToken
{
    public function __construct(private AbstractProvider $provider, private SerdeCommon $serde)
    {
    }

    /**
     * @return callable
     *@throws Error
     */
    public function __invoke(): callable
    {
        /** @var Error|Token $token */
        $token = pipe(
            config('oauth.storage') . '/access_token.json',
            $this->loadToken(...),
            $this->refreshToken(...),
            $this->saveToken(...),
        );

        if ($token instanceof Error) {
            throw $token;
        }

        return static fn (callable $handler): callable =>
            static fn (RequestInterface $request, array $options): mixed => $handler(
                $request->withHeader('Authorization', 'Bearer ' . $token->accessToken),
                $options
            );
    }

    private function loadToken(string $path): Error|Token
    {
        $json = file_get_contents($path);

        if ($json === false) {
            return Error::fromMessage('Unable to read access token.');
        }

        try {
            return Token::fromJson($json);
        } catch (\JsonException $e) {
            return Error::fromThrowable($e, 'Unable to decode access token.');
        }
    }

    private function refreshToken(Token $token): Error|Token
    {
        if ($token->isValid()) {
            return $token;
        }

        try {
            return Token::fromAccessToken(accessToken: $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $token->refreshToken,
            ]));
        } catch (IdentityProviderException $e) {
            return Error::fromThrowable($e, 'Unable to refresh access token.');
        }
    }

    private function saveToken(Token $token): Error|Token
    {
        $bytesStored = file_put_contents(
            filename: config('oauth.storage') . '/access_token.json',
            data: $this->serde->serialize($token, format: 'json'),
        );

        if ($bytesStored === false) {
            return Error::fromMessage('Unable to save access token.');
        }

        return $token;
    }
}
