<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit\Queue;

use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Queue;
use Wearesho\Delivery\Yii2\Tests\ServiceMock;

/**
 * @coversDefaultClass \Wearesho\Delivery\Yii2\Queue\Job
 */
class JobTest extends Delivery\Yii2\Tests\TestCase
{
    private ServiceMock $service;

    private Delivery\Yii2\Repository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceMock();
        \Yii::$container->setSingleton(ServiceMock::class, $this->service);
        $this->repository = new Delivery\Yii2\Repository();
    }

    public function testSleep(): void
    {
        $job = new Queue\Job();

        $this->assertEquals(
            ['service', 'repository', 'item',],
            $job->__sleep()
        );
    }

    public function testSerialization(): void
    {
        $message = new Delivery\Message('Text', '380930000001', [Delivery\Options::SENDER_NAME => 'Sender']);
        $job = $this->createJob(new Queue\Item($message, 'job_1'));

        $restored = unserialize(serialize($job));

        $this->assertInstanceOf(Queue\Job::class, $restored);
        $this->assertEquals($job->service, $restored->service);
        $this->assertEquals($job->repository, $restored->repository);
        $this->assertEquals('job_1', $restored->item->jobId());
        $this->assertEquals($message, $restored->item->message());
    }

    public function testExecute(): void
    {
        $message = new Delivery\Message('Text', '380930000001', [Delivery\Options::SENDER_NAME => 'Sender']);
        $queued = $this->repository->add(
            Queue\Service::NAME,
            new Delivery\Result('job_1', $message, Delivery\Result\Status::Queued)
        );

        $this->createJob(new Queue\Item($message, 'job_1'))
            ->execute($this->createMock(\yii\queue\Queue::class));

        $this->assertEquals([$message], $this->service->messages);

        $item = $this->repository->getById($queued->id());
        $this->assertNotNull($item);
        $this->assertEquals('mock', $item->serviceName());
        $this->assertEquals('mock_1', $item->result()->messageId());
        $this->assertEquals(Delivery\Result\Status::Accepted, $item->result()->status());
        $this->assertEquals($message, $item->result()->message());
        $this->assertNull($this->repository->getByResultId(Queue\Service::NAME, 'job_1'));
    }

    public function testExecuteWithServiceException(): void
    {
        $this->service->exception = new Delivery\Exception('Service is unavailable');
        $message = new Delivery\Message('Text', '380930000001');
        $queued = $this->repository->add(
            Queue\Service::NAME,
            new Delivery\Result('job_1', $message, Delivery\Result\Status::Queued)
        );

        $this->createJob(new Queue\Item($message, 'job_1'))
            ->execute($this->createMock(\yii\queue\Queue::class));

        $item = $this->repository->getById($queued->id());
        $this->assertNotNull($item);
        $this->assertEquals('mock', $item->serviceName());
        $this->assertEquals('job_1', $item->result()->messageId());
        $this->assertEquals(Delivery\Result\Status::Error, $item->result()->status());
        $this->assertEquals('Service is unavailable', $item->result()->reason());
    }

    public function testExecuteWithoutHistoryItem(): void
    {
        $message = new Delivery\Message('Text', '380930000001');

        $this->createJob(new Queue\Item($message, 'job_1'))
            ->execute($this->createMock(\yii\queue\Queue::class));

        $this->assertEquals([$message], $this->service->messages);
        $this->assertEquals(0, Delivery\Yii2\HistoryItem::find()->count());
    }

    private function createJob(Queue\Item $item): Queue\Job
    {
        return new Queue\Job([
            'service' => ServiceMock::class,
            'repository' => Delivery\Yii2\Repository::class,
            'item' => $item,
        ]);
    }
}
