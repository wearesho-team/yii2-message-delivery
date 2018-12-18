<?php

namespace Wearesho\Delivery\Yii2;

use Horat1us\Yii\Traits\BootstrapMigrations;
use yii\base;
use yii\console;
use yii\di;
use Wearesho\Delivery;

/**
 * Class Bootstrap
 * @package Wearesho\Delivery\Yii2
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    use BootstrapMigrations;

    /** @var array|string|Delivery\ServiceInterface definition */
    public $service;

    /**
     * @param base\Application $app
     */
    public function bootstrap($app): void
    {
        $this->setAliases($app);
        $this->configureContainer(\Yii::$container);

        if ($app instanceof console\Application) {
            $this->appendMigrations($app, "Wearesho\\Delivery\\Yii2\\Migrations");
        }
    }

    public function configureContainer(di\Container $container): void
    {
        $repositoryConfigured = $container->has(Delivery\RepositoryInterface::class)
            || $container->hasSingleton(Delivery\RepositoryInterface::class);

        if (!$repositoryConfigured) {
            $container->setSingleton(
                Delivery\RepositoryInterface::class,
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

    public function setAliases(base\Application $application): void
    {
        $application->setAliases([
            '@Wearesho/Delivery/Yii2' => '@vendor/wearesho-team/yii2-message-delivery/src',
        ]);
    }
}
