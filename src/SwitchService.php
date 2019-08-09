<?php

namespace Wearesho\Delivery\Yii2;

use Wearesho\Delivery;
use yii\base;
use yii\di;

/**
 * Class SwitchService
 * @package Wearesho\Delivery\Yii2
 */
class SwitchService extends base\BaseObject implements Delivery\ServiceInterface
{
    /** @var array|string|SwitchService\ConfigInterface reference */
    public $config = [
        'class' => SwitchService\EnvironmentConfig::class,
    ];

    /** @var string[]|array[]|Delivery\ServiceInterface[] definitions */
    public $services;

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
    public function send(Delivery\MessageInterface $message): void
    {
        try {
            $service = $this->getActiveService();
        } catch (base\InvalidConfigException $e) {
            throw new Delivery\Exception("Error while instantiating delivery service.", 0, $e);
        }

        $service->send($message);
    }

    /**
     * @return Delivery\ServiceInterface
     * @throws base\InvalidConfigException
     */
    public function getActiveService(): Delivery\ServiceInterface
    {
        $serviceKey = $this->config->getService();

        if (!array_key_exists($serviceKey, $this->services)) {
            throw new base\InvalidConfigException("Service {$serviceKey} does not configured.");
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return di\Instance::ensure(
            $this->services[$serviceKey],
            Delivery\ServiceInterface::class
        );
    }
}
