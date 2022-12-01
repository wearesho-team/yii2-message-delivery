<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;

class RepositoryServiceTest extends TestCase
{
    protected Delivery\Yii2\RepositoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new Delivery\Yii2\RepositoryService([
            'service' => Delivery\ServiceMock::class,
            'repository' => Delivery\MemoryRepository::class,
        ]);
    }

    public function testSuccessfulSent(): void
    {
        $message = new Delivery\Message('text', 'recipient');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send($message);
        $this->assertTrue($this->service->repository->isSent($message));
    }

    public function testFailSending(): void
    {
        $message = new Delivery\Message('text', 'recipient');
        $this->service->service = new class implements Delivery\ServiceInterface
        {
            public function send(Delivery\MessageInterface $message): void
            {
                throw new Delivery\Exception("Exception Mock");
            }
        };

        $this->expectException(Delivery\Exception::class);
        $this->expectExceptionMessage('Exception Mock');
        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->service->send($message);
        } catch (Delivery\Exception $exception) {
            $this->assertFalse($this->service->repository->isSent($message));
            throw $exception;
        }
    }
}
