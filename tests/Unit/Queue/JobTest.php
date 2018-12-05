<?php

namespace Wearesho\Delivery\Yii2\Tests\Unit\Queue;

use Wearesho\Delivery;

/**
 * Class JobTest
 * @package Wearesho\Delivery\Yii2\Tests\Unit\Queue
 * @internal
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
        $job->execute('queue');

        $this->assertTrue(
            $repository->isSent($message)
        );
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Recipient has to be be a string
     */
    public function testInvalidRecipient(): void
    {
        $service = new Delivery\ServiceMock();
        $job = new Delivery\Yii2\Queue\Job([
            'service' => $service,
            'recipient' => [],
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute('queue');
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Recipient has to be be a string
     */
    public function testEmptyRecipient(): void
    {
        $service = new Delivery\ServiceMock();
        $job = new Delivery\Yii2\Queue\Job([
            'service' => $service,
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute('queue');
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Text has to be be a string
     */
    public function testInvalidText(): void
    {
        $service = new Delivery\ServiceMock();
        $job = new Delivery\Yii2\Queue\Job([
            'service' => $service,
            'recipient' => 'string',
            'text' => [],
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute('queue');
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage Text has to be be a string
     */
    public function testEmptyText(): void
    {
        $service = new Delivery\ServiceMock();
        $job = new Delivery\Yii2\Queue\Job([
            'service' => $service,
            'recipient' => 'string',
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute('queue');
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
