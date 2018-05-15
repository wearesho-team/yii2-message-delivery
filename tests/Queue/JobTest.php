<?php

namespace Wearesho\Delivery\Yii2\Tests\Queue;

use Wearesho\Delivery;

/**
 * Class JobTest
 * @package Wearesho\Delivery\Yii2\Tests\Queue
 * @internal
 * @coversDefaultClass \Wearesho\Delivery\Yii2\Queue\Job
 */
class JobTest extends Delivery\Yii2\Tests\AbstractTestCase
{
    public function testSleep(): void
    {
        $job = new Delivery\Yii2\Queue\Job();

        $this->assertEquals(
            ['service', 'message',],
            $job->__sleep()
        );
    }

    public function testExecute(): void
    {
        $service = new Delivery\ServiceMock();
        $repository = new Delivery\MemoryRepository();
        $service->setRepository($repository);

        $message = new Delivery\Message("Text", "Recipient");

        $job = new Delivery\Yii2\Queue\Job([
            'service' => $service,
            'message' => $message,
        ]);

        $job->execute('queue');

        $this->assertTrue(
            $repository->isSent($message)
        );
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Message have to implement Wearesho\Delivery\MessageInterface
     */
    public function testInvalidMessage(): void
    {
        $service = new Delivery\ServiceMock();
        $job = new Delivery\Yii2\Queue\Job([
            'service' => $service,
            'message' => $service
        ]);
        $job->execute('queue');
    }
}
