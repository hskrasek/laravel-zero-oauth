<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth;

use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\Provider\AbstractProvider;

class LaravelZeroOAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/oauth.php', 'oauth');

        $this->app->singleton(config('oauth.provider'), function () {
            /** @phpstan-var AbstractProvider $provider */
            $provider = config('oauth.provider');

            return new $provider([
                'clientId' => config('oauth.client_id'),
                'clientSecret' => config('oauth.client_secret'),
                'redirectUri' => config('oauth.redirect_uri'),
                'urlAuthorize' => config('oauth.authorize_uri'),
                'urlAccessToken' => config('oauth.token_uri'),
                'scopes' => config('oauth.scopes'),
            ]);
        });
    }
}
