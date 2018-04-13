# Xmrchant

Xmrchant is a Laravel 5 package for developers to easily implement Monero payments into their projects.

## Features

COMING SOON.

## Example Usage

COMING SOON.

## Installation

Add the package to your Laravel app using Composer

```bash
composer require JK3Y/xmrchant
```

Register the package's service provider in `config/app.php`. In Laravel 5.5+ this can be skipped if auto-discovery is enabled.

```php
'providers' => [
	...
	JK3Y\Xmrchant\XmrchantServiceProvider::class,
	...
];
```

The package comes with a Facade, which you can optionally register as well. In Laravel 5.5+ this can be skipped if auto-discovery is enabled.

```php
'aliases' => [
	...
	'Xmrchant' => JK3Y\Xmrchant\Facades\Xmrchant::class,
	...
]
```

Publish the config file (`config/xmrchant.php`) and migration file (`migrations/####_##_##_######_create_xmrchant_tables`) of the package using artisan.

```bash
php artisan vendor:publish --provider="JK3Y\Xmrchant\XmrchantServiceProvider"
```

Run the migrations to add the required tables to your database.
```bash
php artisan migrate
```

## Documentation

COMING SOON.

## License

This package is released under the MIT license (MIT).