<?php

namespace Wearesho\Delivery\Yii2;

use Wearesho\Delivery;
use yii\base;
use yii\di;

/**
 * Class RepositoryService
 * @package Wearesho\Delivery\Yii2
 */
class RepositoryService extends base\BaseObject implements Delivery\ServiceInterface
{
    /** @var array|string|Delivery\ServiceInterface definition */
    public $service = Delivery\ServiceInterface::class;

    /** @var array|string|Delivery\RepositoryInterface definition */
    public $repository = Delivery\RepositoryInterface::class;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->service = di\Instance::ensure($this->service, Delivery\ServiceInterface::class);
        $this->repository = di\Instance::ensure($this->repository, Delivery\RepositoryInterface::class);
    }

    /**
     * @param Delivery\MessageInterface $message
     * @throws \Throwable
     */
    public function send(Delivery\MessageInterface $message): void
    {
        $sender = get_class($this->service);

        try {
            $this->service->send($message);
            $this->repository->push($message, $sender, true);
        } catch (\Throwable $exception) {
            $this->repository->push($message, $sender, false);
            throw $exception;
        }
    }
}
