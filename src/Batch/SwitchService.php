<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Batch;

use Wearesho\Delivery;
use Wearesho\Delivery\BalanceInterface;
use Wearesho\Delivery\ResultInterface;
use yii\base;
use yii\di;

class SwitchService extends base\BaseObject implements Delivery\Batch\ServiceInterface
{
    /** @var array|string|ConfigInterface reference */
    public ConfigInterface|array|string $config = [
        'class' => EnvironmentConfig::class,
    ];

    /** @var string[]|array[]|Delivery\Batch\ServiceInterface[] definitions */
    public array $services;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->config = di\Instance::ensure($this->config, ConfigInterface::class);
    }

    public function name(): string
    {
        return $this->activeService()->name();
    }

    public function balance(): BalanceInterface
    {
        return $this->activeService()->balance();
    }

    public function send(Delivery\MessageInterface $message): ResultInterface
    {
        try {
            $service = $this->activeService();
        } catch (base\InvalidConfigException $e) {
            throw new Delivery\Exception("Error while instantiating delivery service.", 0, $e);
        }

        return $service->send($message);
    }

    public function batch(iterable $messages): iterable
    {
        try {
            $service = $this->activeService();
        } catch (base\InvalidConfigException $e) {
            throw new Delivery\Exception("Error while instantiating delivery service.", 0, $e);
        }

        return $service->batch($messages);
    }

    /**
     * @return Delivery\ServiceInterface
     * @throws base\InvalidConfigException
     */
    private function activeService(): Delivery\Batch\ServiceInterface
    {
        $serviceKey = $this->config->getBatchService();

        if (!array_key_exists($serviceKey, $this->services)) {
            throw new base\InvalidConfigException("Service {$serviceKey} does not configured.");
        }

        /** @var Delivery\Batch\ServiceInterface $service */
        $service = di\Instance::ensure(
            $this->services[$serviceKey],
            Delivery\Batch\ServiceInterface::class
        );
        $this->services[$serviceKey] = $service;

        return $service;
    }
}
