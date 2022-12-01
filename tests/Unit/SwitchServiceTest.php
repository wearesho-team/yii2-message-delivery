<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use PHPUnit\Framework\TestCase;
use yii\base;
use Wearesho\Delivery;

class SwitchServiceTest extends TestCase
{
    /** @var Delivery\ServiceMock[] */
    protected array $services;

    protected Delivery\Yii2\SwitchService $switchService;

    protected function setUp(): void
    {
        parent::setUp();

        for ($i = 0; $i < 2; $i++) {
            $this->services[] = $service = new Delivery\ServiceMock();
            $service->setRepository(new Delivery\MemoryRepository());
        }

        $this->switchService = new Delivery\Yii2\SwitchService([
            'services' => $this->services,
        ]);
    }

    public function testActiveService(): void
    {
        putenv('DELIVERY_SERVICE=0');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->services[0], $this->switchService->getActiveService());
        putenv('DELIVERY_SERVICE=1');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->services[1], $this->switchService->getActiveService());
        putenv('DELIVERY_SERVICE');

        $this->expectException(base\InvalidConfigException::class);
        $this->expectExceptionMessage('Service default does not configured.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->switchService->getActiveService();
    }

    public function testSending(): void
    {
        putenv('DELIVERY_SERVICE=0');

        $message = new Delivery\Message('text', 'recipient');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->switchService->send($message);

        $this->assertTrue($this->services[0]->getRepository()->isSent($message));

        putenv('DELIVERY_SERVICE');
        $this->expectException(Delivery\Exception::class);
        $this->expectExceptionMessage('Error while instantiating delivery service.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->switchService->send($message);
    }
}
