<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit\Batch;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Batch;
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
        putenv('DELIVERY_BATCH_SERVICE');
        parent::tearDown();
    }

    public function testDefaultService(): void
    {
        $service = $this->createService();
        $this->assertInstanceOf(Batch\EnvironmentConfig::class, $service->config);

        $message = new Delivery\Message('text', 'recipient');
        $result = $service->send($message);

        $this->assertEquals('default_1', $result->messageId());
        $this->assertEquals([$message], $this->default->messages);
        $this->assertEmpty($this->second->messages);
        $this->assertEquals('default', $service->name());
        $this->assertEquals(10, $service->balance()->getAmount());
    }

    public function testBatch(): void
    {
        putenv('DELIVERY_BATCH_SERVICE=second');
        $service = $this->createService();

        $messages = [
            new Delivery\Message('first', 'recipient'),
            new Delivery\Message('second', 'recipient'),
        ];
        $results = iterator_to_array($service->batch($messages), false);

        $this->assertEquals(1, $this->second->batchCount);
        $this->assertEquals(0, $this->default->batchCount);
        $this->assertEquals($messages, $this->second->messages);
        $this->assertEquals(
            ['second_1', 'second_2'],
            array_map(fn(Delivery\ResultInterface $result): string => $result->messageId(), $results)
        );
        $this->assertEquals('second', $service->name());
        $this->assertEquals(20, $service->balance()->getAmount());
    }

    public function testServiceDefinition(): void
    {
        $service = new Batch\SwitchService([
            'config' => $this->createConfig(Batch\ConfigInterface::BATCH_SERVICE_DEFAULT),
            'services' => [
                Batch\ConfigInterface::BATCH_SERVICE_DEFAULT => [
                    'class' => ServiceMock::class,
                ],
            ],
        ]);

        $this->assertEquals('mock_1', $service->send(new Delivery\Message('first', 'recipient'))->messageId());
        // instantiated service is reused
        $this->assertEquals('mock_2', $service->send(new Delivery\Message('second', 'recipient'))->messageId());
        $this->assertInstanceOf(
            ServiceMock::class,
            $service->services[Batch\ConfigInterface::BATCH_SERVICE_DEFAULT]
        );
    }

    public function testMissingServiceOnSend(): void
    {
        $service = $this->createService($this->createConfig('missing'));

        $this->expectMissingServiceException(
            fn() => $service->send(new Delivery\Message('text', 'recipient'))
        );
    }

    public function testMissingServiceOnBatch(): void
    {
        $service = $this->createService($this->createConfig('missing'));

        $this->expectMissingServiceException(
            fn() => $service->batch([new Delivery\Message('text', 'recipient')])
        );
    }

    public function testMissingServiceOnName(): void
    {
        $service = $this->createService($this->createConfig('missing'));

        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Service missing does not configured.');
        $service->name();
    }

    private function expectMissingServiceException(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Exception expected for missing service.');
        } catch (Delivery\Exception $exception) {
            $this->assertEquals('Error while instantiating delivery service.', $exception->getMessage());
            $this->assertInstanceOf(base\InvalidConfigException::class, $exception->getPrevious());
            $this->assertEquals('Service missing does not configured.', $exception->getPrevious()->getMessage());
        }
    }

    private function createConfig(string $service): Batch\ConfigInterface
    {
        return new class ($service) implements Batch\ConfigInterface {
            public function __construct(private readonly string $service)
            {
            }

            public function getBatchService(): string
            {
                return $this->service;
            }
        };
    }

    private function createService(?Batch\ConfigInterface $config = null): Batch\SwitchService
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
        return new Batch\SwitchService($definition);
    }
}
