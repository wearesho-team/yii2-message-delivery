<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Batch;

interface ConfigInterface
{
    public const SERVICE_DEFAULT = 'default';

    public function getBatchService(): string;
}
