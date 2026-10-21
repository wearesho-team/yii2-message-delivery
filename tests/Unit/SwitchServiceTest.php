<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\SwitchService;
use Wearesho\Delivery\Yii2\Tests\ServiceMock;
use yii\base;

class SwitchServiceTest extends TestCase
{
    private ServiceMock $default;

    private ServiceMock $second;

    protected function setUp(): void
    {
        parent::setUp();
        $this->default = new ServiceMock('default', 10);
        $this->second = new ServiceMock('second', 20);
    }

    protected function tearDown(): void
    {
        putenv('DELIVERY_SERVICE');
        parent::tearDown();
    }

    public function testDefaultService(): void
    {
        $service = $this->createService();
        $this->assertInstanceOf(SwitchService\EnvironmentConfig::class, $service->config);

        $message = new Delivery\Message('text', 'recipient');
        $result = $service->send($message);

        $this->assertEquals('default_1', $result->messageId());
        $this->assertEquals([$message], $this->default->messages);
        $this->assertEmpty($this->second->messages);
        $this->assertEquals('default', $service->name());
        $this->assertEquals(10, $service->balance()->getAmount());
    }

    public function testEnvironmentConfig(): void
    {
        putenv('DELIVERY_SERVICE=second');
        $service = $this->createService();

        $message = new Delivery\Message('text', 'recipient');
        $result = $service->send($message);

        $this->assertEquals('second_1', $result->messageId());
        $this->assertEmpty($this->default->messages);
        $this->assertEquals([$message], $this->second->messages);
        $this->assertEquals('second', $service->name());
        $this->assertEquals(20, $service->balance()->getAmount());
    }

    public function testConfig(): void
    {
        $service = $this->createService(new SwitchService\Config('second'));

        $this->assertEquals('second', $service->name());
    }

    public function testServiceDefinition(): void
    {
        $service = new Delivery\Yii2\SwitchService([
            'config' => new SwitchService\Config(),
            'services' => [
                SwitchService\ConfigInterface::SERVICE_DEFAULT => [
                    'class' => ServiceMock::class,
                ],
            ],
        ]);

        $this->assertEquals('mock_1', $service->send(new Delivery\Message('first', 'recipient'))->messageId());
        // instantiated service is reused
        $this->assertEquals('mock_2', $service->send(new Delivery\Message('second', 'recipient'))->messageId());
        $this->assertInstanceOf(
            ServiceMock::class,
            $service->services[SwitchService\ConfigInterface::SERVICE_DEFAULT]
        );
    }

    public function testMissingService(): void
    {
        $service = $this->createService(new SwitchService\Config('missing'));

        try {
            $service->send(new Delivery\Message('text', 'recipient'));
            $this->fail('Exception expected for missing service.');
        } catch (Delivery\Exception $exception) {
            $this->assertEquals('Error while instantiating delivery service.', $exception->getMessage());
            $this->assertInstanceOf(base\InvalidConfigException::class, $exception->getPrevious());
            $this->assertEquals('Service missing does not configured.', $exception->getPrevious()->getMessage());
        }

        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Service missing does not configured.');
        $service->name();
    }

    private function createService(?SwitchService\ConfigInterface $config = null): Delivery\Yii2\SwitchService
    {
        $definition = [
            'services' => [
                'default' => $this->default,
                'second' => $this->second,
            ],
        ];
        if ($config !== null) {
            $definition['config'] = $config;
        }
        return new Delivery\Yii2\SwitchService($definition);
    }
}
