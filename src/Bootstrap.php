<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2;

use yii\base;
use yii\console;
use yii\di;
use Wearesho\Delivery;

class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    public Delivery\ServiceInterface|array|string $service;

    /**
     * @param base\Application $app
     * @throws base\InvalidConfigException
     */
    public function bootstrap($app): void
    {
        $this->configureContainer(\Yii::$container);

        if ($app instanceof console\Application) {
            $migrationsBootstrap = new Migrations\Bootstrap();
            $migrationsBootstrap->bootstrap($app);
        }
    }

    public function configureContainer(di\Container $container): void
    {
        $repositoryConfigured = $container->has(Delivery\History\RepositoryInterface::class)
            || $container->hasSingleton(Delivery\History\RepositoryInterface::class);

        if (!$repositoryConfigured) {
            $container->setSingleton(
                Delivery\History\RepositoryInterface::class,
                Delivery\Yii2\Repository::class
            );
        }

        $serviceConfigured = !empty($this->service)
            && (
                $container->has(Delivery\ServiceInterface::class)
                || $container->hasSingleton(Delivery\ServiceInterface::class)
            );

        if (!$serviceConfigured) {
            $container->set(Delivery\ServiceInterface::class, $this->service);
        }
    }
}
