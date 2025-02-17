<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Queue;

use Wearesho\Delivery;

class Item
{
    public function __construct(
        private readonly Delivery\MessageInterface $message,
        private readonly string $jobId,
    ) {
    }

    public function jobId(): string
    {
        return $this->jobId;
    }

    public function message(): Delivery\MessageInterface
    {
        return $this->message;
    }
}
