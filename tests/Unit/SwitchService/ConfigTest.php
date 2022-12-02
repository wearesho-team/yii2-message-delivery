<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit\SwitchService;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;

class ConfigTest extends TestCase
{
    public function testGetServiceDefault(): void
    {
        $config = new Delivery\Yii2\SwitchService\Config();
        $this->assertEquals(
            Delivery\Yii2\SwitchService\ConfigInterface::SERVICE_DEFAULT,
            $config->getService()
        );
    }

    public function testGetService(): void
    {
        $service = "Now" . microtime(true);
        $config = new Delivery\Yii2\SwitchService\Config($service);
        $this->assertEquals($service, $config->getService());
    }
}
