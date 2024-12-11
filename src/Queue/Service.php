<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Queue;

use Wearesho\Delivery;
use yii\base;
use yii\di;
use yii\queue\Queue;

class Service extends base\BaseObject implements Delivery\ServiceInterface
{
    public const OPTION_SYNC = 'queue.sync';

    /** @var string|array|Queue */
    public $queue = 'queue';

    /** @var array Delivery\ServiceInterface configuration */
    public $service;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        // @codeCoverageIgnoreStart
        if (!class_exists(Queue::class)) {
            throw new base\InvalidConfigException(
                "You have to install yiisoft/yii2-queue before use " . static::class
            );
        }
        // @codeCoverageIgnoreEnd

        $this->queue = di\Instance::ensure($this->queue, Queue::class);
    }

    /**
     * @param Delivery\MessageInterface $message
     * @throws base\InvalidConfigException
     */
    public function send(Delivery\MessageInterface $message): void
    {
        if (empty($this->service) || (!is_array($this->service) && !is_string($this->service))) {
            throw new base\InvalidConfigException(
                "You must configure service as string or array before usage"
            );
        }

        if ($message instanceof Delivery\MessageOptionsInterface) {
            $options = $message->getOptions();
            if (array_key_exists(self::OPTION_SYNC, $options) && ($options[self::OPTION_SYNC] === true)) {
                $this->sendSync($message);
                return;
            }
        }

        di\Instance::ensure($this->service, Delivery\ServiceInterface::class);

        $job = new Delivery\Yii2\Queue\Job();

        $job->service = $this->service;
        $job->recipient = $message->getRecipient();
        $job->text = $message->getText();

        if ($message instanceof Delivery\ContainsSenderName) {
            $job->senderName = $message->getSenderName();
        } elseif ($message instanceof Delivery\MessageOptionsInterface) {
            $job->options = $message->getOptions();
        }

        $this->queue->push($job);
    }

    private function sendSync(Delivery\MessageInterface $message): void
    {
        /** @var Delivery\ServiceInterface $service */
        $service = di\Instance::ensure($this->service, Delivery\ServiceInterface::class);
        $service->send($message);
    }
}
