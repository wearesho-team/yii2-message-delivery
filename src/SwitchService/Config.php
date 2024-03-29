<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\SwitchService;

class Config implements ConfigInterface
{
    protected string $service;

    public function __construct(string $service = ConfigInterface::SERVICE_DEFAULT)
    {
        $this->service = $service;
    }

    public function getService(): string
    {
        return $this->service;
    }
}
