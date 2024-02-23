# Laravel Zero OAuth

[![example workflow](https://github.com/hskrasek/laravel-zero-oauth/actions/workflows/tests.yml/badge.svg)](https://github.com/hskrasek/laravel-zero-oauth/actions)
[![Latest Stable Version](https://poser.pugx.org/hskrasek/laravel-zero-oauth/v)](//packagist.org/packages/hskrasek/laravel-zero-oauth)
[![Total Downloads](https://poser.pugx.org/hskrasek/laravel-zero-oauth/downloads)](//packagist.org/packages/hskrasek/laravel-zero-oauth)
[![PHP Version Require](https://img.shields.io/packagist/php-v/hskrasek/laravel-zero-oauth.svg?style=flat-square)](https://packagist.org/packages/hskrasek/laravel-zero-oauth)
[![GitHub stars](https://img.shields.io/github/stars/hskrasek/laravel-zero-oauth.svg?style=flat-square)](https://github.com/hskrasek/laravel-zero-oauth)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/hskrasek/laravel-zero-oauth/blob/master/LICENSE)

## Introduction

**Laravel Zero OAuth** is a library aimed at adding OAuth 2.0 authentication to your [Laravel Zero](http://laravel-zero.com) application, leveraging [The PHP League's OAuth 2.0 Client](https://oauth2-client.thephpleague.com/) library.

Laravel Zero was created by Nuno [Nuno Maduro](https://github.com/nunomaduro) and [Owen Voke](https://github.com/owenvoke), and is a micro-framework that provides an elegant starting point for your console application. It's a customized version of Laravel optimized for building command-line applications.

## Installation

* To get started install it using composer:

```sh
composer require hskrasek/laravel-zero-oauth
```

* Then add `LaravelZeroOAuthProvider` to your `config/app.php` file:

```diff
'providers' => [
    App\Providers\AppServiceProvider::class,
++  HSkrasek\LaravelZeroOAuth\LaravelZeroOAuthProvider::class,
],
```

## Usage

To get started you'll want to configure your OAuth provider. This package supports all providers that are supported by [The PHP League's OAuth 2.0 Client](https://oauth2-client.thephpleague.com/), and you can find a list of providers and their configuration options [here](https://oauth2-client.thephpleague.com/providers/league/). You can do this by configuring the provider in your `.env` file:

```dotenv
OAUTH2_CLIENT_ID=<CLIENT_ID>
OAUTH2_CLIENT_SECRET=<CLIENT_SECRET>
OAUTH2_SCOPES=<SCOPES>
OAUTH2_AUTHORIZE_URI=<AUTHORIZE_URI>
OAUTH2_TOKEN_URI=<TOKEN_URI>

# Your redirect URI needs to match your applications OAuth configuration. It is recommended to use the default value, but you can change it if necessary.
# The package will attempt to correctly start a server for you using PHP's built in server.
# OAUTH2_REDIRECT_URI="http://127.0.0.1:8000"

# By default the package will use the GenericProvider, but you can change it to any provider supported by The PHP League's OAuth 2.0 Client.
# OAUTH2_PROVIDER=League\OAuth2\Client\Provider\GenericProvider::class
```

Once you've configured your provider, you can use the `oauth:login` command to start the OAuth flow:

```sh
php your-app-name oauth:login
```

## Credits

* [All Contributors](../../contributors)

## License

Laravel Zero OAuth is open-sourced software licensed under the [MIT license](LICENSE.md).
