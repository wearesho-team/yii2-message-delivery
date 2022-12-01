<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use Wearesho\Delivery;

class BootstrapTest extends Delivery\Yii2\Tests\TestCase
{
    protected array $aliases;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aliases = \Yii::$aliases;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Yii::$aliases = $this->aliases;
    }

    public function testBootstrap(): void
    {
        $bootstrap = new Delivery\Yii2\Bootstrap();
        $bootstrap->bootstrap(\Yii::$app);
        $this->assertEquals(
            dirname(dirname(__DIR__)) . '/src/Migrations',
            \Yii::getAlias('@Wearesho/Delivery/Yii2/Migrations')
        );
    }

    public function testBootstrapWithService(): void
    {
        \Yii::$container->set(Delivery\ServiceInterface::class, Delivery\ServiceMock::class);
        $bootstrap = new Delivery\Yii2\Bootstrap();
        $bootstrap->service = new Delivery\ServiceMock();
        $bootstrap->bootstrap(\Yii::$app);

        $service = \Yii::$container->get(Delivery\ServiceInterface::class);
        $this->assertInstanceOf(Delivery\ServiceMock::class, $service);
    }
}
