<?php

declare(strict_types=1);

use LaravelZero\Framework\Application;
use League\OAuth2\Client\Provider\AbstractProvider;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once base_path() . '/bootstrap/app.php';

$app->make(abstract: Illuminate\Contracts\Console\Kernel::class)
    ->bootstrap();

/** @var AbstractProvider $provider */
$provider = $app->make(config('oauth.provider'));

$accessToken = $provider->getAccessToken(grant: 'authorization_code', options: [
    'code' => $_GET['code'],
]);

file_put_contents(
    filename: config('oauth.storage') . '/access_token.json',
    data: json_encode($accessToken, flags: JSON_PRETTY_PRINT)
);

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
