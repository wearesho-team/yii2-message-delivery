<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Queue;

use yii\base;
use yii\queue;
use yii\di;
use Wearesho\Delivery;

class Job extends base\BaseObject implements queue\JobInterface
{
    /**
     * @see Delivery\ServiceInterface
     * @var array|string array or string definition
     */
    public $service;

    /** @var string */
    public string $recipient;

    /** @var string */
    public string $text;

    /** @var string */
    public string $senderName;

    public ?array $options = null;

    /**
     * @param Queue\Queue $queue which pushed and is handling the job
     * @throws base\InvalidConfigException
     * @throws Delivery\Exception
     */
    public function execute($queue): void
    {
        /** @var Delivery\ServiceInterface $service */
        $service = di\Instance::ensure($this->service, Delivery\ServiceInterface::class);

        $service->send($this->getMessage());
    }

    public function getMessage(): Delivery\MessageInterface
    {
        if (is_array($this->options)) {
            return new Delivery\MessageWithOptions($this->text, $this->recipient, $this->options);
        }

        return empty($this->senderName)
            ? new Delivery\Message($this->text, $this->recipient)
            : new Delivery\MessageWithSender($this->text, $this->recipient, $this->senderName);
    }

    public function __sleep(): array
    {
        return ['service', 'recipient', 'text', 'senderName',];
    }
}
