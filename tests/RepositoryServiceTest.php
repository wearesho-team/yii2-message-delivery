<?php

namespace Wearesho\Delivery\Yii2\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;


/**
 * Class RepositoryServiceTest
 * @package Wearesho\Delivery\Yii2\Tests
 */
class RepositoryServiceTest extends TestCase
{
    /** @var Delivery\Yii2\RepositoryService */
    protected $service;

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
        $this->service->send($message);
        $this->assertTrue($this->service->repository->isSent($message));
    }

    /**
     * @expectedException \Wearesho\Delivery\Exception
     * @expectedExceptionMessage Exception Mock
     */
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

        try {
            $this->service->send($message);
        } catch (Delivery\Exception $exception) {
            $this->assertFalse($this->service->repository->isSent($message));
            throw $exception;
        }
    }
}
