<?php

declare(strict_types=1);

use Crell\Serde\SerdeCommon;
use HSkrasek\LaravelZeroOAuth\Auth\Keyring;
use HSkrasek\LaravelZeroOAuth\Token;
use LaravelZero\Framework\Application;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/app.php';

$app->make(abstract: Illuminate\Contracts\Console\Kernel::class)
    ->bootstrap();

/** @var AbstractProvider $provider */
$provider = $app->make(config('oauth.provider'));

try {
    $accessToken = $provider->getAccessToken(grant: 'authorization_code', options: [
        'code' => $_GET['code'] ?? '',
    ]);
} catch (IdentityProviderException | UnexpectedValueException $e) {
    header(header: 'Content-Type: text/html', response_code: 401);
    echo <<<HTML
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Unauthorized</title>
    </head>
    <body>
        <h1>Unauthorized</h1>
        <p>Authorization unsuccessful. {$e->getMessage()}</p>
    </body>
</html>
HTML;

    if (\function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } elseif (\function_exists('litespeed_finish_request')) {
        litespeed_finish_request();
    } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
        $status = \ob_get_status(true);
        $level = \count($status);
        $flags = \PHP_OUTPUT_HANDLER_REMOVABLE | \PHP_OUTPUT_HANDLER_FLUSHABLE;

        while ($level-- > 0 && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            ob_end_flush();
        }

        flush();
    }

    $app->terminate();

    exit;
}

$token = Token::fromAccessToken(accessToken: $accessToken);

$app->make(Keyring::class)
    ->add(name: 'access_token', token: $token);

header(header: 'Content-Type: text/html', response_code: 200);
echo <<<HTML
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Authorization</title>
    </head>
    <body>
        <h1>Authorization</h1>
        <p>Authorization successful. You may now close this window.</p>
    </body>
</html>
HTML;

if (\function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} elseif (\function_exists('litespeed_finish_request')) {
    litespeed_finish_request();
} elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    $status = \ob_get_status(true);
    $level = \count($status);
    $flags = \PHP_OUTPUT_HANDLER_REMOVABLE | \PHP_OUTPUT_HANDLER_FLUSHABLE;

    while ($level-- > 0 && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
        ob_end_flush();
    }

    flush();
}

$app->terminate();
