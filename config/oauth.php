<?php

declare(strict_types=1);

use League\OAuth2\Client\Provider\GenericProvider;

return [
    'provider' => env('OAUTH2_PROVIDER', GenericProvider::class),

    'client_id' => env('OAUTH2_CLIENT_ID'),

    'client_secret' => env('OAUTH2_CLIENT_SECRET'),

    'redirect_uri' => env('OAUTH2_REDIRECT_URI'),

    'authorize_uri' => env('OAUTH2_AUTHORIZE_URI'),

    'token_uri' => env('OAUTH2_TOKEN_URI'),

    'scopes' => env('OAUTH2_SCOPES'),
];
