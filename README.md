# GoSms notifications channel for Laravel 5.3+


[![Latest Version on Packagist](https://img.shields.io/packagist/v/shiroamada/gosms.svg?style=flat-square)](https://packagist.org/packages/shiroamada/gosms)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/shiroamada/gosms/master.svg?style=flat-square)](https://travis-ci.org/shiroamada/gosms)
[![StyleCI](https://styleci.io/repos/108503043/shield)](https://styleci.io/repos/108503043)
[![Quality Score](https://img.shields.io/scrutinizer/g/laravel-notification-channels/smsc-ru.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/smsc-ru)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/laravel-notification-channels/smsc-ru/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/shiroamada/gosms/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/shiroamada/gosms.svg?style=flat-square)](https://packagist.org/packages/shiroamada/gosms)

This package makes it easy to send notifications using [gosms.com.my](//gosms.com.my) with Laravel 5.3+.

Code Reference laravel-notification-channels/smsc-ru

## Contents

- [Installation](#installation)
    - [Setting up the SmscRu service](#setting-up-the-gosms-service)
- [Usage](#usage)
    - [Available Message methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

You can install the package via composer:

```bash
composer require shiroamada/gosms
```

Then you must install the service provider:
```php
// config/app.php
'providers' => [
    ...
    NotificationChannels\GoSms\GoSmsServiceProvider::class,
],
```

### Setting up the GoSMS service

Add your gosms.com.my login, secret key (hashed password) and default sender name (or phone number) to your `config/services.php`:

```php
// config/services.php
...
'gosms' => [
    'company'  => env('GOSMS_COMPANY'),
    'username'  => env('GOSMS_USERNAME'),
    'password'  => env('GOSMS_PASSWORD'),
    'sender' => 'John_Doe'
],
...
```

## Usage

You can use the channel in your `via()` method inside the notification:

```php
use Illuminate\Notifications\Notification;
use NotificationChannels\GoSms\GoSmsMessage;
use NotificationChannels\GoSms\GoSmsChannel;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [GoSmsChannel::class];
    }

    public function toGoSms($notifiable)
    {
        return GoSmsMessage::create("Task #{$notifiable->id} is complete!");
    }
}
```

In your notifiable model, make sure to include a routeNotificationForGoSms() method, which return the phone number.

```php
public function routeNotificationForGoSms()
{
    return $this->mobile;
}
```

### Available methods

`from()`: Sets the sender's name or phone number.

`content()`: Set a content of the notification message.

`sendAt()`: Set a time for scheduling the notification message.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please use the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [ShiroAmada](https://github.com/shiroamada)
- [JhaoDa](https://github.com/jhaoda)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
