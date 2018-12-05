<?php

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use Wearesho\Delivery;

/**
 * Class BootstrapTest
 * @package Wearesho\Delivery\Yii2\Tests\Unit
 */
class BootstrapTest extends Delivery\Yii2\Tests\TestCase
{
    protected $aliases;

    protected function setUp()
    {
        parent::setUp();


        $this->aliases = \Yii::$aliases;
    }

    protected function tearDown()
    {
        parent::tearDown();

        \Yii::$aliases = $this->aliases;
    }

    public function testBootstrap(): void
    {
        $bootstrap = new Delivery\Yii2\Bootstrap();
        $bootstrap->bootstrap($this->app);
        $this->assertEquals(
            \Yii::getAlias('@vendor/wearesho-team/yii2-message-delivery/src'),
            \Yii::getAlias('@Wearesho/Delivery/Yii2')
        );
    }

    public function testBootstrapWithService(): void
    {
        \Yii::$container->set(Delivery\ServiceInterface::class, Delivery\ServiceMock::class);
        $bootstrap = new Delivery\Yii2\Bootstrap();
        $bootstrap->service = new Delivery\ServiceMock();
        $bootstrap->bootstrap($this->app);
        $this->assertEquals(
            \Yii::getAlias('@vendor/wearesho-team/yii2-message-delivery/src'),
            \Yii::getAlias('@Wearesho/Delivery/Yii2')
        );
    }
}
