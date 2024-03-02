<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth\Http\Middleware;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

final readonly class RefreshToken
{
    public function __construct(private AbstractProvider $provider)
    {
    }

    /**
     * @throws IdentityProviderException|RuntimeException
     * @return callable
     */
    public function __invoke(): callable
    {
        $accessTokenJson = file_get_contents(
            filename: config('oauth.storage') . '/access_token.json',
        );

        if ($accessTokenJson === false) {
            throw new RuntimeException('Unable to read access token.');
        }

        try {
            /** @var array{token_type: string, access_token: string, refresh_token: string, expires: string} $existingToken */
            $existingToken = json_decode($accessTokenJson, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException('Unable to decode access token.', previous: $e);
        }

        $token = $existingToken['access_token'];

        if ($existingToken['expires'] < time()) {
            $token = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $existingToken['refresh_token'],
            ])->getToken();
        }

        return static fn (callable $handler): callable =>
            static function (RequestInterface $request, array $options) use ($handler, $token): mixed {
                return $handler(
                    $request->withHeader('Authorization', 'Bearer ' . $token),
                    $options
                );
            };
    }
}
