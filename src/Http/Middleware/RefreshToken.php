<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth\Http\Middleware;

use HSkrasek\LaravelZeroOAuth\Auth\Keyring;
use HSkrasek\LaravelZeroOAuth\Error;
use HSkrasek\LaravelZeroOAuth\Token;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\RequestInterface;

use function Crell\fp\pipe;

final readonly class RefreshToken
{
    public function __construct(
        private Keyring $keyring
    ) {
    }

    /**
     * @return callable
     *@throws Error
     */
    public function __invoke(): callable
    {
        /** @var Token|Error $token */
        $token = pipe(
            $this->keyring->get('access_token'),
            $this->refreshToken(...)
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

    private function refreshToken(?Token $token): Error|Token
    {
        if ($token === null) {
            return Error::fromMessage('No access token available to refresh.');
        }

        try {
            return $this->keyring->refresh($token);
        } catch (IdentityProviderException $e) {
            return Error::fromThrowable($e, 'Unable to refresh access token.');
        }
    }
}
