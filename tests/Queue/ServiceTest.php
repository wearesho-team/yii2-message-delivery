<?php

namespace Wearesho\Delivery\Yii2\Tests\Queue;

use Wearesho\Delivery;
use yii\queue;

/**
 * Class ServiceTest
 * @package Wearesho\Delivery\Yii2\Tests\Queue
 * @coversDefaultClass \Wearesho\Delivery\Yii2\Queue\Service
 */
class ServiceTest extends Delivery\Yii2\Tests\AbstractTestCase
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

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage You must configure service as string or array before usage
     */
    public function testInvalidService(): void
    {
        $subService = new Delivery\ServiceMock();
        $this->service->service = $subService;
        $this->service->send(new Delivery\Message('text', 'recipient'));
    }

    public function testSend(): void
    {
        $repository = new Delivery\MemoryRepository();

        $this->container->setSingleton(
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
}
