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
    public array|string $service;
    /**
     * @see Delivery\History\RepositoryInterface
     * @var array|string array or string definition
     */
    public array|string $repository;

    public Item $item;

    /**
     * @param Queue\Queue $queue which pushed and is handling the job
     * @throws base\InvalidConfigException
     * @throws Delivery\Exception
     */
    public function execute($queue): void
    {
        /** @var Delivery\ServiceInterface $service */
        $service = di\Instance::ensure($this->service, Delivery\ServiceInterface::class);
        /** @var Delivery\History\RepositoryInterface $repository */
        $repository = di\Instance::ensure(
            $this->repository,
            Delivery\History\RepositoryInterface::class
        );

        $result = $service->send($this->item->message());
        $historyItem = $repository->getByResultId(Service::NAME, $this->item->jobId());
        if (!is_null($historyItem)) {
            $repository->update($historyItem, $result, $service->name());
        }
    }

    public function __sleep(): array
    {
        return ['service', 'repository', 'item',];
    }
}
