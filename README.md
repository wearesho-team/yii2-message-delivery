# Yii2 Message Delivery
[![Test & Lint](https://github.com/wearesho-team/yii2-message-delivery/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/wearesho-team/yii2-message-delivery/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/yii2-message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/yii2-message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/yii2-message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/yii2-message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-message-delivery)

This repository includes [RepositoryInterface](https://github.com/wearesho-team/message-delivery/blob/1.2.0/src/RepositoryInterface.php)
implementation using Yii2 ActiveRecord

## Installation

```bash
composer require wearesho-team/yii2-message-delivery:^1.8.0
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
composer require yiisoft/yii2-queue:^2.0
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
                'service' => Delivery\ServiceMock::class, // you your custom Delivery\ServiceInterface implementation
            ],
        ],
    ],
];
```
*Note: messages sent using Queue\Service have to correct work with serialize() and unserialize().
See yii2-queue for details*

### SwitchService
You can configure few delivery services, and choose one of them using environment variable.
```php
<?php

use Wearesho\Delivery;
use App;

$service = new Delivery\Yii2\SwitchService([
    'environmentKeyPrefix' => 'DELIVERY_', // by default,
    'services' => [
        'default' => [
            'class' => Delivery\ServiceMock::class,
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

### RepositoryService
You can wrap any your service into RepositoryService, that has repository.
If sending message in wrapped service did not throwed any exception message will be stored as sent.

```php
<?php

use Wearesho\Delivery;
use App;

$service = new Delivery\Yii2\RepositoryService([
    'service' => App\CustomService::class,
    'repository' => Delivery\Yii2\Repository::class, // or your own implementation
]);

// do what you want using Delivery\ServiceInterface
```

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
