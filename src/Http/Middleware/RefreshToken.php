<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth\Http\Middleware;

use HSkrasek\LaravelZeroOAuth\Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

use function Crell\fp\pipe;

final readonly class RefreshToken
{
    public function __construct(private AbstractProvider $provider)
    {
    }

    /**
     * @throws RuntimeException
     * @return callable
     */
    public function __invoke(): callable
    {
        /** @var string $token */
        $token = pipe(
            config('oauth.storage') . '/access_token.json',
            $this->loadToken(...),
            $this->refreshToken(...),
        );

        return static fn (callable $handler): callable =>
            static fn(RequestInterface $request, array $options): mixed => $handler(
                $request->withHeader('Authorization', 'Bearer ' . $token),
                $options
            );
    }

    private function loadToken(string $path): Token
    {
        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException('Unable to read access token.');
        }

        return Token::fromJson($json);
    }

    private function refreshToken(Token $token): string
    {
        if ($token->isValid()) {
            return $token->accessToken;
        }

        return $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->refreshToken,
        ])->getToken();
    }
}
