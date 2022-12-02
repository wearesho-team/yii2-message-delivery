<?php

namespace Wearesho\Delivery\Yii2\Tests\Unit\Queue;

use Wearesho\Delivery;
use yii\queue;
use yii\base;

/**
 * Class ServiceTest
 * @package Wearesho\Delivery\Yii2\Tests\Unit\Queue
 * @coversDefaultClass \Wearesho\Delivery\Yii2\Queue\Service
 */
class ServiceTest extends Delivery\Yii2\Tests\TestCase
{
    /** @var Delivery\Yii2\Queue\Service */
    protected $service;

    /** @var queue\sync\Queue */
    protected $queue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queue = new queue\sync\Queue();
        $this->service = new Delivery\Yii2\Queue\Service([
            'queue' => $this->queue,
        ]);
    }

    public function testInvalidService(): void
    {
        $subService = new Delivery\ServiceMock();
        $this->service->service = $subService;

        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('You must configure service as string or array before usage');
        $this->service->send(new Delivery\Message('text', 'recipient'));
    }

    public function testSend(): void
    {
        $repository = new Delivery\MemoryRepository();

        \Yii::$container->setSingleton(
            Delivery\ServiceMock::class,
            function () use ($repository): Delivery\ServiceMock {
                return new Delivery\ServiceMock($repository);
            }
        );
        $this->service->service = [
            'class' => Delivery\ServiceMock::class,
        ];

        $message = new Delivery\Message('text', 'recipient');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send($message);
        $this->queue->run();

        $this->assertTrue(
            $repository->isSent($message)
        );
    }

    public function testCreateWithSender(): void
    {
        $repository = new Delivery\MemoryRepository();

        \Yii::$container->setSingleton(
            Delivery\ServiceMock::class,
            function () use ($repository): Delivery\ServiceMock {
                return new Delivery\ServiceMock($repository);
            }
        );
        $this->service->service = [
            'class' => Delivery\ServiceMock::class,
        ];

        $message = new Delivery\MessageWithSender('text', 'recipient', 'sender');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send($message);
        $this->queue->run();

        /** @var Delivery\HistoryItem $item */
        $item = $repository->getHistoryItem($message);
        $this->assertEquals('sender', $item->getSenderName());
    }
}
