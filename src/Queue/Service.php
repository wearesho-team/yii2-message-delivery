<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Queue;

use Wearesho\Delivery;
use Wearesho\Delivery\BalanceInterface;
use yii\base;
use yii\di;
use yii\queue\Queue;

class Service extends base\BaseObject implements Delivery\ServiceInterface
{
    public const NAME = 'yii2.queue';

    /** @var string|array|Queue */
    public Queue|array|string $queue = 'queue';

    /** @var array Delivery\ServiceInterface configuration */
    public array $service;

    /** @var array Delivery\History\RepositoryInterface configuration */
    public array $repository = [
        'class' => Delivery\History\RepositoryInterface::class,
    ];

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

        if (empty($this->service)) {
            throw new base\InvalidConfigException(
                "You must configure service as array before usage"
            );
        }

        if (empty($this->repository)) {
            throw new base\InvalidConfigException(
                "You must configure repository as array before usage"
            );
        }
    }

    public function name(): string
    {
        return static::NAME;
    }

    public function balance(): BalanceInterface
    {
        return $this->getSyncService()->balance();
    }

    /**
     * @param Delivery\MessageInterface $message
     * @throws base\InvalidConfigException
     */
    public function send(Delivery\MessageInterface $message): Delivery\ResultInterface
    {
        if (Delivery\Options::get($message, Options::SYNC) === true) {
            return $this->sendSync($message);
        }

        di\Instance::ensure($this->service, Delivery\ServiceInterface::class);

        $job = new Delivery\Yii2\Queue\Job();

        $job->service = $this->service;
        $job->repository = $this->repository;
        $job->item = new Item(
            message: $rawMessage = new Delivery\Message(
                text: $message->getText(),
                recipient: $message->getRecipient(),
                options: $message->getOptions()
            ),
            jobId: $jobId = uniqid('', true),
        );

        $this->queue->push($job);

        $result = new Delivery\Result(
            $jobId,
            $rawMessage,
            Delivery\Result\Status::Queued
        );

        $this->getRepository()->add($this->name(), $result);

        return $result;
    }

    private function sendSync(Delivery\MessageInterface $message): Delivery\ResultInterface
    {
        return $this->getSyncService()->send($message);
    }

    private function getSyncService(): Delivery\ServiceInterface
    {
        /** @var Delivery\ServiceInterface $baseService */
        $baseService = di\Instance::ensure($this->service, Delivery\ServiceInterface::class);
        /** @var Delivery\History\RepositoryInterface $repository */
        $repository = di\Instance::ensure($this->repository, Delivery\History\RepositoryInterface::class);

        return new Delivery\History\Service(
            baseService: Delivery\Batch\Service::wrap($baseService),
            repository: $repository,
        );
    }

    private function getRepository(): Delivery\History\RepositoryInterface
    {
        /** @var Delivery\History\RepositoryInterface $repository */
        $repository = di\Instance::ensure(
            $this->repository,
            Delivery\History\RepositoryInterface::class
        );
        return $repository;
    }
}
