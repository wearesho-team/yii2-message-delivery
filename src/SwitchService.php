<?php

namespace Wearesho\Delivery\Yii2;

use yii\base;
use yii\di;
use Wearesho\Delivery;
use Horat1us\Environment;

/**
 * Class SwitchService
 * @package Wearesho\Delivery\Yii2
 */
class SwitchService extends base\BaseObject implements Delivery\ServiceInterface
{
    use Environment\ConfigTrait;

    public $environmentKeyPrefix = 'DELIVERY_';

    /** @var string[]|array[]|Delivery\ServiceInterface[] definitions */
    public $services;

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
        $environmentService = $this->getEnv('SERVICE', 'default');

        if (!array_key_exists($environmentService, $this->services)) {
            throw new base\InvalidConfigException("Service {$environmentService} does not configured.");
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return di\Instance::ensure(
            $this->services[$environmentService],
            Delivery\ServiceInterface::class
        );
    }

    protected function getEnvironmentKeyPrefix(): string
    {
        return $this->environmentKeyPrefix;
    }
}
