# Yii2 Message Delivery
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/message-delivery)
[![Build Status](https://travis-ci.org/wearesho-team/message-delivery.svg?branch=master)](https://travis-ci.org/wearesho-team/message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/message-delivery)

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

## Authors

- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
