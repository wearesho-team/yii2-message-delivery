<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit\Queue;

use PHPUnit\Framework\Attributes\CoversClass;
use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Tests\ServiceMock;
use yii\base;
use yii\queue;

#[CoversClass(Delivery\Yii2\Queue\Service::class)]
class ServiceTest extends Delivery\Yii2\Tests\TestCase
{
    private queue\sync\Queue $queue;

    private ServiceMock $mock;

    private Delivery\Yii2\Repository $repository;

    private Delivery\Yii2\Queue\Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queue = new queue\sync\Queue();
        $this->mock = new ServiceMock('mock', 100);
        \Yii::$container->setSingleton(ServiceMock::class, $this->mock);
        $this->repository = new Delivery\Yii2\Repository();
        $this->service = new Delivery\Yii2\Queue\Service([
            'queue' => $this->queue,
            'service' => [
                'class' => ServiceMock::class,
            ],
            'repository' => [
                'class' => Delivery\Yii2\Repository::class,
            ],
        ]);
    }

    public function testMissingService(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('You must configure service as array before usage');

        new Delivery\Yii2\Queue\Service([
            'queue' => $this->queue,
        ]);
    }

    public function testMissingRepository(): void
    {
        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('You must configure repository as array before usage');

        new Delivery\Yii2\Queue\Service([
            'queue' => $this->queue,
            'service' => [
                'class' => ServiceMock::class,
            ],
            'repository' => [],
        ]);
    }

    public function testName(): void
    {
        $this->assertEquals(Delivery\Yii2\Queue\Service::NAME, $this->service->name());
    }

    public function testBalance(): void
    {
        $this->assertEquals(100, $this->service->balance()->getAmount());
    }

    public function testSend(): void
    {
        $message = new Delivery\Message('text', '380930000001', [
            Delivery\Options::SENDER_NAME => 'sender',
        ]);

        $result = $this->service->send($message);

        $this->assertEquals(Delivery\Result\Status::Queued, $result->status());
        $this->assertEquals($message, $result->message());
        $this->assertEmpty($this->mock->messages);

        $queued = $this->repository->getByResultId(Delivery\Yii2\Queue\Service::NAME, $result->messageId());
        $this->assertNotNull($queued);
        $this->assertEquals(Delivery\Result\Status::Queued, $queued->result()->status());

        $this->queue->run();

        $this->assertEquals([$message], $this->mock->messages);
        $this->assertEquals(1, Delivery\Yii2\HistoryItem::find()->count());

        $item = $this->repository->getById($queued->id());
        $this->assertNotNull($item);
        $this->assertEquals('mock', $item->serviceName());
        $this->assertEquals('mock_1', $item->result()->messageId());
        $this->assertEquals(Delivery\Result\Status::Accepted, $item->result()->status());
        $this->assertEquals($message, $item->result()->message());
    }

    public function testSendSync(): void
    {
        $message = new Delivery\Message('text', '380930000001', [
            Delivery\Yii2\Queue\Options::SYNC => true,
        ]);

        // queue is not run to ensure message is sent without queue usage
        $result = $this->service->send($message);

        $this->assertEquals(Delivery\Result\Status::Accepted, $result->status());
        $this->assertEquals('mock_1', $result->messageId());
        $this->assertEquals([$message], $this->mock->messages);
        $this->assertEquals(1, Delivery\Yii2\HistoryItem::find()->count());

        $item = $this->repository->getByResultId('mock', 'mock_1');
        $this->assertNotNull($item);
        $this->assertEquals(Delivery\Result\Status::Accepted, $item->result()->status());
        $this->assertEquals($message, $item->result()->message());
    }
}
