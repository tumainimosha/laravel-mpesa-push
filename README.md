# Mpesa (Tz) Push API - Laravel Package

*** This is still a work in progress and some applications are still undergoing tests.

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Travis](https://img.shields.io/travis/tumainimosha/laravel-mpesa-push.svg?style=flat-square)]()
[![Total Downloads](https://img.shields.io/packagist/dt/tumainimosha/laravel-mpesa-push.svg?style=flat-square)](https://packagist.org/packages/tumainimosha/laravel-mpesa-push)

## Install

Install via composer

```bash
composer require tumainimosha/laravel-mpesa-push
```

### Publish Configuration File

Publish config file to customize the default package config.

```bash
php artisan vendor:publish --provider="Tumainimosha\MpesaPush\MpesaPushServiceProvider" --tag="config"
```

### Publish Migrations

```bash
php artisan vendor:publish --provider="Tumainimosha\MpesaPush\MpesaPushServiceProvider" --tag="migrations"
php artisan migrate
```

## Configuration

### Authentication

Configure your api username and password in `.env` file as follows

```dotenv
TZ_MPESA_PUSH_SSL_CERT_PASSWORD=secret
TZ_MPESA_PUSH_USERNAME=123123
TZ_MPESA_PUSH_PASSWORD=VeryStrongPasswd
TZ_MPESA_PUSH_BUSINESS_NAME=FooCompany
TZ_MPESA_PUSH_BUSINESS_NUMBER=123123
```

Other configuration can be found in the config file published by this package. The options are well commented 😊

## Usage

```php

use Tumainimosha\MpesaPush\MpesaPush;

...

public function testPush() {

    // Resolve service object
    $push = MpesaPush::instance();
    
    $customerMsisdn = '<substitute valid mpesa-tz number>';
    $amount = 250;
    $txnId = str_random('20');
    
    $responseCode = $push->postRequest($customerMsisdn, $amount, $txnId);
    
    // Check for response code
    // Valid response codes
    //  '0' - Success (note: response code is string '0' not numeric 0)
    //  'Duplicate' - Duplicate transaction ID
    //   Others - fail
    
}
    
```

### Handling callback

Out of the box, this package stores transactions in table `mpesa_push_transactions`, and updates their status on receiving callback.

However, you may need to do further actions on your app after receiving callback, by listening to event `MpesaCallbackReceived::class` fired at callback.

You need to implement your own event listener to listen for this event and do any additional steps after receiving callback.

The event has public attribute `$transaction` which contains the transaction parameters including status

```php
// EventServiceProvider.php

protected $listen = [
    ...
    \Tumainimosha\MpesaPush\Events\MpesaCallbackReceived::class => [
        \App\Listeners\MpesaCallbackReceivedHandler::class,
    ],
];

// MpesaCallbackReceivedHandler.php

public function handle(MpesaCallbackReceived $event)
{
    $transaction = $event->transaction;
    
    // do your custom logic here
}

### Customize config values at runtime

The service offers fluent setters to change config values at runtime if your use case requires.

Such a use case could be when you have multiple accounts on the same project, and you fetch your config values from DB.

```php

// $account here could be a Model fetched from db with account attributes
$pushService = MpesaPush::instance();

$pushService->setUsername($account->username)
    ->setPassword($account->password)
    ->setBusinessName($account->business_name)
    ->setBusinessNumber($account->business_number)
    ->setCommand($account->command);
    
$pushService->postRequest($customerMsisdn, $amount, $txnId);
```

## Testing
Run the tests with:

``` bash
vendor/bin/phpunit
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### TODO
- [X] Login
- [X] Push Transaction request
- [X] Callback processing

## Security
If you discover any security-related issues, please email princeton.mosha@gmail.com instead of using the issue tracker.

## License
The MIT License (MIT). Please see [License File](/LICENSE.md) for more information.
