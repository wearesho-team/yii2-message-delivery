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
    public Delivery\History\RepositoryInterface|array|string $repository = Repository::class;
    public Delivery\Batch\ServiceInterface|array|string|null $batchService = null;

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

    public function configureContainer(
        di\Container $container
    ): void {
        $repository = $this->getRepositoryDefinition($container);
        if (!is_null($repository)) {
            $container->setSingleton(
                Delivery\History\RepositoryInterface::class,
                $repository
            );
        }

        $service = $this->getServiceDefinition($container);
        if (!is_null($service)) {
            $container->set(Delivery\ServiceInterface::class, $service);
        }

        $batchService = $this->getBatchServiceDefinition($container);
        if (!is_null($batchService)) {
            $container->set(Delivery\Batch\ServiceInterface::class, $batchService);
        }
    }

    protected function getRepositoryDefinition(
        di\Container $container
    ): Delivery\History\RepositoryInterface|array|string|null {
        if (
            $container->has(Delivery\History\RepositoryInterface::class)
            || $container->hasSingleton(Delivery\History\RepositoryInterface::class)
        ) {
            return null;
        }
        return $this->repository;
    }

    protected function getServiceDefinition(
        di\Container $container
    ): Delivery\Batch\ServiceInterface|array|string|null {
        if (
            $container->has(Delivery\ServiceInterface::class)
            || $container->hasSingleton(Delivery\ServiceInterface::class)
        ) {
            return null;
        }

        return $this->service;
    }

    protected function getBatchServiceDefinition(
        di\Container $container
    ): Delivery\Batch\ServiceInterface|array|string|null {
        if (
            empty($this->batchService)
            || $container->has(Delivery\Batch\ServiceInterface::class)
            || $container->hasSingleton(Delivery\Batch\ServiceInterface::class)
        ) {
            return null;
        }

        return $this->batchService;
    }
}
