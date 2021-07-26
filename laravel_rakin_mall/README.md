# POSCAR SME

<p align="center">
<img src="https://i.imgur.com/NHFTsGt.png">
</p>

## Features

- Nuxt 2
- Laravel 8
- SPA or SSR
- Socialite integration
- VueI18n + ESlint + Bootstrap 4 + Font Awesome 5
- Login, register, email verification and password reset

## Installation

- `php artisan key:generate`
- `php artisan jwt:secret`
- `php artisan migrate`
- `npm install`

## Usage

### Development

```bash
# start Laravel
php artisan serve
```


## Socialite

This project comes with GitHub as an example for [Laravel Socialite](https://laravel.com/docs/5.8/socialite).

To enable the provider create a new GitHub application and use `https://example.com/api/oauth/github/callback` as the Authorization callback URL.

Edit `.env` and set `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` with the keys form your GitHub application.

For other providers you may need to set the appropriate keys in `config/services.php` and redirect url in `OAuthController.php`.

## Email Verification

To enable email verification make sure that your `App\User` model implements the `Illuminate\Contracts\Auth\MustVerifyEmail` contract.

## Notes

- This project uses [router-module](https://github.com/nuxt-community/router-module), so you have to add the routes manually in `client/router.js`.
- If you want to separate this in two projects (client and server api), move `package.json` into `client/` and remove config path option from the scripts section. Also make sure to add the env variables in `client/.env`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.
