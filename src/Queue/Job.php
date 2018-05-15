<?php

namespace Wearesho\Delivery\Yii2\Queue;

use yii\base;
use yii\queue;
use yii\di;
use Wearesho\Delivery;

/**
 * Class Job
 * @package Wearesho\Delivery\Yii2\Queue
 */
class Job extends base\BaseObject implements queue\JobInterface
{
    /**
     * @see Delivery\ServiceInterface
     * @var array array or string definition
     */
    public $service;

    /**
     * @var Delivery\MessageInterface
     */
    public $message;

    /**
     * @param Queue\Queue $queue which pushed and is handling the job
     * @throws base\InvalidConfigException
     * @throws Delivery\Exception
     */
    public function execute($queue): void
    {
        /** @var Delivery\ServiceInterface $service */
        $service = di\Instance::ensure($this->service, Delivery\ServiceInterface::class);

        if (!$this->message instanceof Delivery\MessageInterface) {
            throw new base\InvalidConfigException(
                "Message have to implement " . Delivery\MessageInterface::class
            );
        }

        $service->send($this->message);
    }

    public function __sleep(): array
    {
        return ['service', 'message'];
    }
}
