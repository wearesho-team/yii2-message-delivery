<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Tests\ServiceMock;
use yii\console;
use yii\di;
use yii\web;

class BootstrapTest extends TestCase
{
    private array $aliases;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aliases = \Yii::$aliases;
        \Yii::$container = new di\Container();
        \Yii::$app = new console\Application([
            'id' => 'bootstrap-test',
            'basePath' => dirname(__DIR__, 2),
        ]);
    }

    protected function tearDown(): void
    {
        \Yii::$app = null;
        \Yii::$container = new di\Container();
        \Yii::$aliases = $this->aliases;

        parent::tearDown();
    }

    public function testBootstrapConsoleApplication(): void
    {
        $bootstrap = new Delivery\Yii2\Bootstrap([
            'service' => ServiceMock::class,
        ]);
        $bootstrap->bootstrap(\Yii::$app);

        $this->assertEquals(
            dirname(__DIR__, 2) . '/src/Migrations',
            \Yii::getAlias('@Wearesho/Delivery/Yii2/Migrations')
        );
        $this->assertContains(
            'Wearesho\\Delivery\\Yii2\\Migrations',
            \Yii::$app->controllerMap['migrate']['migrationNamespaces']
        );
        $this->assertInstanceOf(
            Delivery\Yii2\Repository::class,
            \Yii::$container->get(Delivery\History\RepositoryInterface::class)
        );
        $this->assertInstanceOf(
            ServiceMock::class,
            \Yii::$container->get(Delivery\ServiceInterface::class)
        );
    }

    public function testBootstrapWebApplication(): void
    {
        $bootstrap = new Delivery\Yii2\Bootstrap([
            'service' => ServiceMock::class,
        ]);
        $bootstrap->bootstrap($this->createMock(web\Application::class));

        $this->assertFalse(\Yii::getAlias('@Wearesho/Delivery/Yii2/Migrations', false));
        $this->assertInstanceOf(
            ServiceMock::class,
            \Yii::$container->get(Delivery\ServiceInterface::class)
        );
    }

    public function testConfigureContainer(): void
    {
        $container = new di\Container();
        $bootstrap = new Delivery\Yii2\Bootstrap([
            'service' => ServiceMock::class,
            'batchService' => [
                'class' => ServiceMock::class,
            ],
        ]);
        $bootstrap->configureContainer($container);

        $this->assertTrue($container->hasSingleton(Delivery\History\RepositoryInterface::class));
        $this->assertInstanceOf(
            Delivery\Yii2\Repository::class,
            $container->get(Delivery\History\RepositoryInterface::class)
        );
        $this->assertInstanceOf(
            ServiceMock::class,
            $container->get(Delivery\ServiceInterface::class)
        );
        $this->assertInstanceOf(
            ServiceMock::class,
            $container->get(Delivery\Batch\ServiceInterface::class)
        );
    }

    public function testConfigureContainerWithoutBatchService(): void
    {
        $container = new di\Container();
        $bootstrap = new Delivery\Yii2\Bootstrap([
            'service' => ServiceMock::class,
        ]);
        $bootstrap->configureContainer($container);

        $this->assertTrue($container->has(Delivery\ServiceInterface::class));
        $this->assertFalse($container->has(Delivery\Batch\ServiceInterface::class));
    }

    public function testConfigureContainerKeepsExistingDefinitions(): void
    {
        $repository = new Delivery\History\MemoryRepository();
        $service = new ServiceMock('existing');
        $batchService = new ServiceMock('existing-batch');

        $container = new di\Container();
        $container->setSingleton(Delivery\History\RepositoryInterface::class, $repository);
        $container->set(Delivery\ServiceInterface::class, fn(): ServiceMock => $service);
        $container->setSingleton(Delivery\Batch\ServiceInterface::class, $batchService);

        $bootstrap = new Delivery\Yii2\Bootstrap([
            'service' => ServiceMock::class,
            'batchService' => ServiceMock::class,
        ]);
        $bootstrap->configureContainer($container);

        $this->assertSame($repository, $container->get(Delivery\History\RepositoryInterface::class));
        $this->assertSame($service, $container->get(Delivery\ServiceInterface::class));
        $this->assertSame($batchService, $container->get(Delivery\Batch\ServiceInterface::class));
    }
}
