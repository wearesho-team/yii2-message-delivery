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

    /** @var string */
    public $recipient;

    /** @var string */
    public $text;

    /** @var string */
    public $senderName;

    /**
     * @param Queue\Queue $queue which pushed and is handling the job
     * @throws base\InvalidConfigException
     * @throws Delivery\Exception
     */
    public function execute($queue): void
    {
        /** @var Delivery\ServiceInterface $service */
        $service = di\Instance::ensure($this->service, Delivery\ServiceInterface::class);

        if (!is_string($this->recipient)) {
            throw new base\InvalidConfigException("Recipient has to be be a string");
        }
        if (!is_string($this->text)) {
            throw new base\InvalidConfigException("Text has to be be a string");
        }

        $service->send($this->getMessage());
    }

    public function getMessage(): Delivery\MessageInterface
    {
        return empty($this->senderName)
            ? new Delivery\Message($this->text, $this->recipient)
            : new Delivery\MessageWithSender($this->text, $this->recipient, $this->senderName);
    }

    public function __sleep(): array
    {
        return ['service', 'recipient', 'text', 'senderName',];
    }
}
