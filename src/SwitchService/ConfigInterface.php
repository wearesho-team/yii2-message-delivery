<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\SwitchService;

/**
 * Interface ConfigInterface
 * @package Wearesho\Delivery\Yii2\SwitchService
 */
interface ConfigInterface
{
    public const SERVICE_DEFAULT = 'default';

    public function getService(): string;
}
