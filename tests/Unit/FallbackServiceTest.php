<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Tests\ServiceMock;
use yii\caching;

class FallbackServiceTest extends TestCase
{
    private const FAILED_CACHE_KEY = 'sms_service_failed_primary';

    private ServiceMock $primary;

    private ServiceMock $fallback;

    private caching\ArrayCache $cache;

    private Delivery\Yii2\FallbackService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->primary = new ServiceMock('primary', 10);
        $this->fallback = new ServiceMock('fallback', 20);
        $this->cache = new caching\ArrayCache();
        $this->service = new Delivery\Yii2\FallbackService([
            'primary' => $this->primary,
            'fallback' => $this->fallback,
            'cache' => $this->cache,
        ]);
    }

    public function testSendWithPrimary(): void
    {
        $message = new Delivery\Message('text', 'recipient');

        $this->assertEquals('primary', $this->service->name());
        $this->assertEquals('primary_1', $this->service->send($message)->messageId());
        $this->assertEquals([$message], $this->primary->messages);
        $this->assertEmpty($this->fallback->messages);
        $this->assertEquals('primary', $this->service->name());
        $this->assertFalse($this->cache->get(self::FAILED_CACHE_KEY));
    }

    public function testSendWithFallbackOnPrimaryFailure(): void
    {
        $this->primary->exception = new Delivery\Exception('Primary is unavailable');
        $message = new Delivery\Message('text', 'recipient');

        $this->assertEquals('fallback_1', $this->service->send($message)->messageId());
        $this->assertEquals('fallback', $this->service->name());
        $this->assertTrue($this->cache->get(self::FAILED_CACHE_KEY));

        // primary is not used while it is marked as failed
        $this->primary->exception = null;
        $this->assertEquals('fallback_2', $this->service->send($message)->messageId());
        $this->assertEmpty($this->primary->messages);
        $this->assertEquals([$message, $message], $this->fallback->messages);
    }

    public function testSendWithPrimaryOnFallbackFailure(): void
    {
        $this->cache->set(self::FAILED_CACHE_KEY, true);
        $this->fallback->exception = new Delivery\Exception('Fallback is unavailable');
        $message = new Delivery\Message('text', 'recipient');

        $this->assertEquals('fallback', $this->service->name());
        $this->assertEquals('primary_1', $this->service->send($message)->messageId());
        $this->assertEquals('primary', $this->service->name());
        $this->assertFalse($this->cache->get(self::FAILED_CACHE_KEY));
    }

    public function testBalance(): void
    {
        $this->assertEquals(10, $this->service->balance()->getAmount());

        $this->primary->exception = new Delivery\Exception('Primary is unavailable');
        $this->assertEquals(20, $this->service->balance()->getAmount());
        $this->assertTrue($this->cache->get(self::FAILED_CACHE_KEY));

        $this->primary->exception = null;
        $this->fallback->exception = new Delivery\Exception('Fallback is unavailable');
        $this->assertEquals(10, $this->service->balance()->getAmount());
        $this->assertFalse($this->cache->get(self::FAILED_CACHE_KEY));
    }
}
