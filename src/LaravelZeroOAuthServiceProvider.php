<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth;

use Crell\Serde\SerdeCommon;
use HSkrasek\LaravelZeroOAuth\Auth\Keyring;
use HSkrasek\LaravelZeroOAuth\Commands\Auth\Login;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;

class LaravelZeroOAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->publishes([
            __DIR__ . '/../config/oauth.php' => config_path('oauth.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/oauth.php', 'oauth');

        $this->app->singleton(config('oauth.provider'), function (): AbstractProvider {
            /** @phpstan-var AbstractProvider $provider */
            $provider = config('oauth.provider');

            return new $provider([
                'clientId' => config('oauth.auth.client_id'),
                'clientSecret' => config('oauth.auth.client_secret'),
                'redirectUri' => config('oauth.auth.redirect_uri'),
                'urlAuthorize' => config('oauth.auth.authorize_uri'),
                'urlAccessToken' => config('oauth.auth.token_uri'),
                'scopes' => config('oauth.auth.scopes'),
                'urlResourceOwnerDetails' => '',
                'scopeSeparator' => ' ',
            ], ['optionProvider' => new HttpBasicAuthOptionProvider(),]);
        });

        $this->app->singleton(Keyring::class, fn (): Keyring => new Keyring(
            $this->app->make(config('oauth.provider')),
            new SerdeCommon(),
            config('oauth.storage')
        ));

        $this->app->when(Login::class)
            ->needs(AbstractProvider::class)
            ->give(config('oauth.provider'));

        $this->commands([
            Login::class,
        ]);
    }
}
