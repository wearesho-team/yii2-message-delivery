# Yii2 Message Delivery
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/yii2-message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/yii2-message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/yii2-message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/yii2-message-delivery)
[![Build Status](https://travis-ci.org/wearesho-team/yii2-message-delivery.svg?branch=master)](https://travis-ci.org/wearesho-team/yii2-message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-message-delivery)

This repository includes [RepositoryInterface](https://github.com/wearesho-team/message-delivery/blob/1.2.0/src/RepositoryInterface.php)
implementation using Yii2 ActiveRecord

## Installation

```bash
composer require wearesho-team/yii2-message-delivery:^1.0.0
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

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
