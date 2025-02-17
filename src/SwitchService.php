<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2;

use Wearesho\Delivery;
use yii\base;
use yii\di;

class SwitchService extends base\BaseObject implements Delivery\ServiceInterface
{
    /** @var array|string|SwitchService\ConfigInterface reference */
    public SwitchService\ConfigInterface|array|string $config = [
        'class' => SwitchService\EnvironmentConfig::class,
    ];

    /** @var string[]|array[]|Delivery\ServiceInterface[] definitions */
    public array $services;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->config = di\Instance::ensure($this->config, SwitchService\ConfigInterface::class);
    }

    /**
     * @param Delivery\MessageInterface $message
     * @throws Delivery\Exception
     */
    public function send(Delivery\MessageInterface $message): Delivery\ResultInterface
    {
        try {
            $service = $this->activeService();
        } catch (base\InvalidConfigException $e) {
            throw new Delivery\Exception("Error while instantiating delivery service.", 0, $e);
        }

        return $service->send($message);
    }

    public function name(): string
    {
        return $this->activeService()->name();
    }

    public function balance(): Delivery\BalanceInterface
    {
        return $this->activeService()->balance();
    }

    /**
     * @return Delivery\ServiceInterface
     * @throws base\InvalidConfigException
     */
    private function activeService(): Delivery\ServiceInterface
    {
        $serviceKey = $this->config->getService();

        if (!array_key_exists($serviceKey, $this->services)) {
            throw new base\InvalidConfigException("Service {$serviceKey} does not configured.");
        }

        /** @var Delivery\ServiceInterface $service */
        $service = di\Instance::ensure(
            $this->services[$serviceKey],
            Delivery\ServiceInterface::class
        );
        $this->services[$serviceKey] = $service;

        return $service;
    }
}
