<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\SwitchService;

use Horat1us\Environment;

class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    public function __construct(string $keyPrefix = 'DELIVERY_')
    {
        parent::__construct($keyPrefix);
    }

    public function getService(): string
    {
        return $this->getEnv('SERVICE', ConfigInterface::SERVICE_DEFAULT);
    }
}
