<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Batch;

use Horat1us\Environment;

class EnvironmentConfig extends Environment\Yii2\Config implements ConfigInterface
{
    public $keyPrefix = 'DELIVERY_BATCH_';

    public function getService(): string
    {
        return $this->getEnv('SERVICE', static::SERVICE_DEFAULT);
    }
}
