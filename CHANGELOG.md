# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v0.0.3] - 2024-06-29
### :sparkles: New Features
- [`a7e4da9`](https://github.com/hskrasek/laravel-zero-oauth/commit/a7e4da9f449cccfceae5b958f4b45feee05b95f0) - **http**: Add Illuminate/Http to require-dev and suggest in composer.json *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`93ec6a1`](https://github.com/hskrasek/laravel-zero-oauth/commit/93ec6a1851d015125fe980940f3da6246f0496d1) - **Token**: Add Token class for handling OAuth tokens *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`19aee03`](https://github.com/hskrasek/laravel-zero-oauth/commit/19aee0359e1c81b882ccae2cd9fa913fe1dc38a4) - **Error**: Add Error class for handling exceptions *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`121f815`](https://github.com/hskrasek/laravel-zero-oauth/commit/121f815fd3ebe768fd2cebba2d3b3eb7f3087a34) - **server**: Update server to better persist the fetched token *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`1571f34`](https://github.com/hskrasek/laravel-zero-oauth/commit/1571f344e3703e29dfd63a495ffd2df73d00864f) - **Auth**: Add Keyring class for managing OAuth tokens *(commit by [@hskrasek](https://github.com/hskrasek))*

### :bug: Bug Fixes
- [`7d9c3a6`](https://github.com/hskrasek/laravel-zero-oauth/commit/7d9c3a6455c29f74a57809a4667f9200954bb03e) - **server**: Handle exceptions and unauthorized access *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`f83fe3b`](https://github.com/hskrasek/laravel-zero-oauth/commit/f83fe3b22eae172c38df0cc5859650d39d48e3f9) - **Error**: Fix missing getPrevious() method call *(commit by [@hskrasek](https://github.com/hskrasek))*

### :recycle: Refactors
- [`98e573a`](https://github.com/hskrasek/laravel-zero-oauth/commit/98e573a0141fd98aa1d42fdad55eca1631211a26) - **refreshToken**: Improve error handling and token serialization *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`86367b5`](https://github.com/hskrasek/laravel-zero-oauth/commit/86367b5ac9b4c0a28084d6005e90459106c10fb0) - **Token**: Update Token class constructor and add type hints *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`0342852`](https://github.com/hskrasek/laravel-zero-oauth/commit/03428523a10f252020f16a6940c9bc6582e3c9ca) - **auth**: Add service container binding for the new Keyring class *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`28a6577`](https://github.com/hskrasek/laravel-zero-oauth/commit/28a65778c501f3462575fd89a565b94de34b3699) - **auth**: use Keyring for token management *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`0ca79b5`](https://github.com/hskrasek/laravel-zero-oauth/commit/0ca79b537f92b0c8c7310629e3864a94e6323432) - Update dependencies and improve Keyring saveToken *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`685a710`](https://github.com/hskrasek/laravel-zero-oauth/commit/685a7100c9012ee548718ba13c845aa5d1a835a4) - **auth**: update keyring and login logic *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`baa576c`](https://github.com/hskrasek/laravel-zero-oauth/commit/baa576cb2434cb4696757deb2440076f94d024b8) - Update phpstan config and Login command *(commit by [@hskrasek](https://github.com/hskrasek))*

### :wrench: Chores
- [`6e6740c`](https://github.com/hskrasek/laravel-zero-oauth/commit/6e6740c227686dc49e375252f70d08c7f6db591e) - **deps**: update composer.json dependencies *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`b6c73d1`](https://github.com/hskrasek/laravel-zero-oauth/commit/b6c73d1d7bc1156845692d78d2c813ade34a1225) - remove tests path from phpstan config *(commit by [@hskrasek](https://github.com/hskrasek))*


## [v0.0.2] - 2024-02-23
### :recycle: Refactors
- [`c45231b`](https://github.com/hskrasek/laravel-zero-oauth/commit/c45231bc864106b630dced9936429839557c67b2) - **commands**: Update login command signature dynamically *(commit by [@hskrasek](https://github.com/hskrasek))*
- [`8c50134`](https://github.com/hskrasek/laravel-zero-oauth/commit/8c50134585073102922082a91bcb21c50d9393aa) - **auth**: Update redirectUri use toString method *(commit by [@hskrasek](https://github.com/hskrasek))*


[v0.0.2]: https://github.com/hskrasek/laravel-zero-oauth/compare/v0.0.1...v0.0.2
[v0.0.3]: https://github.com/hskrasek/laravel-zero-oauth/compare/v0.0.2...v0.0.3
