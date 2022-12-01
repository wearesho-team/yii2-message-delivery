<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit\Queue;

use Wearesho\Delivery;
use yii\base;
use yii\queue\Queue;

/**
 * @coversDefaultClass \Wearesho\Delivery\Yii2\Queue\Job
 */
class JobTest extends Delivery\Yii2\Tests\TestCase
{
    public function testSleep(): void
    {
        $job = new Delivery\Yii2\Queue\Job();

        $this->assertEquals(
            ['service', 'recipient', 'text', 'senderName',],
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
            'text' => $message->getText(),
            'recipient' => $message->getRecipient(),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute($this->createMock(Queue::class));

        $this->assertTrue(
            $repository->isSent($message)
        );
    }

    public function testSendWithSender(): void
    {
        $job = new Delivery\Yii2\Queue\Job([
            'text' => 'Test text',
            'recipient' => '380000000001',
            'senderName' => 'Custom sender name',
        ]);

        $this->assertInstanceOf(Delivery\ContainsSenderName::class, $job->getMessage());
    }

    public function testEmptySender(): void
    {
        $job = new Delivery\Yii2\Queue\Job([
            'text' => 'text',
            'recipient' => '380000000001',
        ]);
        $this->assertNotInstanceOf(Delivery\ContainsSenderName::class, $job->getMessage());
    }
}
