# Yii2 Message Delivery
[![Test & Lint](https://github.com/wearesho-team/yii2-message-delivery/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/wearesho-team/yii2-message-delivery/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/yii2-message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/yii2-message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/yii2-message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/yii2-message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-message-delivery)

This repository includes [History\RepositoryInterface](https://github.com/wearesho-team/message-delivery/blob/master/src/History/RepositoryInterface.php)
implementation using Yii2 ActiveRecord

Compatibility: tested on PHP 8.4, PHP 8.5

## Installation

```bash
composer require wearesho-team/yii2-message-delivery:^3.2
```

## Usage
### Configuration
```php
<?php
// common/config/main.php

use Wearesho\Delivery;

return [
    'bootstrap' => [
        Delivery\Yii2\Bootstrap::class, // registers migrations and configures container        
    ],
];
```

### Queue
This package provides optional [yii2-queue](https://github.com/yiisoft/yii2-queue) integration.
To use it you have to install yii2-queue package:
```bash
composer require yiisoft/yii2-queue:^2.3.8
```
Then you may configure your application:
```php
<?php
// common/config/main.php

use Wearesho\Delivery;

return [
    'bootstrap' => [
        [
            'class' => Delivery\Yii2\Bootstrap::class,
            'service' => [
                'class' => Delivery\Yii2\Queue\Service::class,
                'service' => Delivery\StubService::class, // your custom Delivery\ServiceInterface implementation
            ],
        ],
    ],
];
```
*Note: messages sent using Queue\Service have to correct work with serialize() and unserialize().
See yii2-queue for details*

#### Skipping queue while sending message
Sometimes there is need to send message without queue (for example during mass messaging in CLI environment).
To skip queue service you need to create message with the `Delivery\Yii2\Queue\Options::SYNC` option:

```php
<?php

use Wearesho\Delivery;

/** @var Delivery\Yii2\Queue\Service $service */
$message = new Delivery\Message(
    text: 'Message Text',
    recipient: '380930000000',
    options: [
        Delivery\Yii2\Queue\Options::SYNC => true,
    ]
);

$service->send($message); // message will be sent directly to sync service without queue job
```

### SwitchService
You can configure few delivery services, and choose one of them using environment variable.
```php
<?php

use Wearesho\Delivery;
use App;

$service = new Delivery\Yii2\SwitchService([
    'config' => [
        'class' => Delivery\Yii2\SwitchService\EnvironmentConfig::class,
        'keyPrefix' => 'DELIVERY_', // by default
    ],
    'services' => [
        'default' => [
            'class' => Delivery\StubService::class,
        ],
        'production' => [
            'class' => App\Delivery\Service::class, // some Delivery\ServiceInterface implementation
        ],
    ],
]);

putenv('DELIVERY_SERVICE'); // clean environment
$message = new Delivery\Message('text', 'recipient');
$service->send($message); // default service will be used if no environment variable set
putenv('DELIVERY_SERVICE=production');
$service->send($message); // production service will be used if it was configured

```

### History
To store the results of sent messages, wrap your service into `Delivery\History\Service`
(from [wearesho-team/message-delivery](https://github.com/wearesho-team/message-delivery))
with this package's repository:

```php
<?php

use Wearesho\Delivery;
use App;

$service = new Delivery\History\Service(
    baseService: Delivery\Batch\Service::wrap(new App\CustomService()),
    repository: new Delivery\Yii2\Repository(), // or your own implementation
);

// do what you want using Delivery\Batch\ServiceInterface
```

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
