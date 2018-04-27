# Xmrchant

Xmrchant is a Laravel 5 package for developers to easily implement Monero payments into their projects.

## Features

Includes migration tables, a cron job to manage incoming transactions, clean easy-to-read account and payment data, and a facade for easy Laravel 5.5+ integration.

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

#### wallet()
Returns an instance of the wallet RPC.

#### height()
Return the current block height.

#### account($account_index)
Look up account information for a given account. Default index is 0.

#### accounts()
Retrieve all accounts from the RPC.

#### new_account($user_id, $label)
Creates a new Monero account if the user requests one for Monero deposits. Allows for an optional label.
```php
Xmrchant::new_account(Auth::id());
```
Returns the created account.

#### address()
Retrieve the addresses for the admin wallet.

#### addresses($account_index)
Retrieve the addresses for a given account index.

#### balance($account_index)
Retrieve the balance for a given account index.

#### balances()
Retrieve the total balance for the admin wallet.

#### get_transfers($account_index, $filterOptions)
Retrieve transfers for a given account index with optional filtering. Default index is 0, no filter options are enabled by default except for 'filter_by_height'.

#### get_transfer_by_txid($txid)
Retrieve a given transfer by txid.

#### transfer($address, $amount)
Transfer funds between two accounts

#### userHasAccount($user_id)
Check if user has a monero account with the website.
```php
$account = Xmrchant::userHasAccount(Auth::id());
```
Returns false if no account exists, or returns with the user's wallet information. If the current active address for that account is used, a new one is generated.

#### fromAtomic($amount)
Converts atomic units into easier-to-read format (0.0000000).

#### toAtomic($amount)
Converts amount into atomic units.

## License

This package is released under the MIT license (MIT).