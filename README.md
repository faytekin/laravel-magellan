<div align="center">
    <br>
    <br>
    <br>
    <br>
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="art/logo_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="art/logo_light.svg">
        <img
            width="60%"
            alt="The logo for laravel-magellan, which shows a small blue paper ship with the package name right beside it." src="art/logo_light.png"
        >
    </picture>
    <br>
    <br>
    <p style="font-size: 1.75rem;">A modern PostGIS toolbox for Laravel</p>
    <br>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/clickbar/laravel-magellan.svg?style=flat-square)](https://packagist.org/packages/clickbar/laravel-magellan)
[![Total Downloads](https://img.shields.io/packagist/dt/clickbar/laravel-magellan.svg?style=flat-square)](https://packagist.org/packages/clickbar/laravel-magellan)
[![GitHub Tests Action Status](https://github.com/clickbar/laravel-magellan/actions/workflows/run-tests.yml/badge.svg)](https://github.com/clickbar/laravel-magellan/actions/workflows/run-tests.yml)
[![GitHub Code Style Action Status](https://github.com/clickbar/laravel-magellan/actions/workflows/fix-styling.yml/badge.svg)](https://github.com/clickbar/laravel-magellan/actions/workflows/fix-styling.yml)
<br>
<br>
</div>

## Introduction

Every sailor needs a nice ship to travel the seven seas ⛵️  

This package will have you prepared for accessing [PostGIS](https://postgis.net) related functionality within Laravel.
It was heavily inspired by [mstaack/laravel-postgis](https://github.com/mstaack/laravel-postgis), but has evolved into much more since. Other than some slight changes, you should be familiar with Magellan very quickly.

Magellan comes with batteries included and also provides parsers/generators for GeoJSON, WKB & WKT out of the box. Easily use all PostGIS datatypes in your migrations and avoid raw SQL to access PostGIS functions by using our Builder functions.

Additionally `laravel-magellan` provides extensions to the Schema, Query Builder and Postgres Grammar for easy access of PostGIS database functions like `ST_EXTENT`. It does all this without breaking compatibility to other packages, like [tpetry/laravel-postgresql-enhanced](https://github.com/tpetry/laravel-postgresql-enhanced), which has to extend the Grammar and Connection.

## Installation

You can install the package via composer:

```bash
composer require clickbar/laravel-magellan
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="magellan-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="magellan-config"
```

You may find the contents of the published config file here:
[config/magellan.php](config/magellan.php)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email [security@clickbar.dev](mailto:security@clickbar.dev) instead of using the issue tracker.

## Credits

- [Adrian](https://github.com/ahawlitschek)
- [saibotk](https://github.com/saibotk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
