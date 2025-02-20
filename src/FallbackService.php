<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2;

use Wearesho\Delivery;
use yii\caching;
use yii\base;
use yii\di;

class FallbackService extends base\BaseObject implements Delivery\ServiceInterface
{
    private const DEFAULT_CACHE_KEY_PREFIX = 'sms_service_failed_';
    private const DEFAULT_CACHE_DURATION = 300; // 5 minutes default timeout
    private const DEFAULT_LOG_CATEGORY = 'app\\message-delivery';

    public Delivery\ServiceInterface|array|string $primary;
    public Delivery\ServiceInterface|array|string $fallback;
    public caching\CacheInterface|array|string $cache = 'cache';
    public int $cacheDuration = self::DEFAULT_CACHE_DURATION;
    public string $cacheKeyPrefix = self::DEFAULT_CACHE_KEY_PREFIX;
    public string $logCategory = self::DEFAULT_LOG_CATEGORY;

    private ?Delivery\ServiceInterface $lastUsedService = null;

    public function name(): string
    {
        if ($this->lastUsedService !== null) {
            return $this->lastUsedService->name();
        }

        return $this->isPrimaryAvailable()
            ? $this->getPrimary()->name()
            : $this->getFallback()->name();
    }

    public function balance(): Delivery\BalanceInterface
    {
        if ($this->isPrimaryAvailable()) {
            try {
                $result = $this->getPrimary()->balance();
                $this->lastUsedService = $this->getPrimary();
                return $result;
            } catch (Delivery\Exception $e) {
                $this->logException($e);
                $this->markPrimaryAsFailed();
                $result = $this->getFallback()->balance();
                $this->lastUsedService = $this->getFallback();
                return $result;
            }
        }

        try {
            $result = $this->getFallback()->balance();
            $this->lastUsedService = $this->getFallback();
            return $result;
        } catch (Delivery\Exception $e) {
            $this->logException($e);
            $this->clearPrimaryFailedState();
            $result = $this->getPrimary()->balance();
            $this->lastUsedService = $this->getPrimary();
            return $result;
        }
    }

    public function send(Delivery\MessageInterface $message): Delivery\ResultInterface
    {
        if ($this->isPrimaryAvailable()) {
            try {
                $result = $this->getPrimary()->send($message);
                $this->lastUsedService = $this->getPrimary();
                return $result;
            } catch (Delivery\Exception $e) {
                $this->logException($e);
                $this->markPrimaryAsFailed();
                $result = $this->getFallback()->send($message);
                $this->lastUsedService = $this->getFallback();
                return $result;
            }
        }

        try {
            $result = $this->getFallback()->send($message);
            $this->lastUsedService = $this->getFallback();
            return $result;
        } catch (Delivery\Exception $e) {
            $this->logException($e);
            $this->clearPrimaryFailedState();
            $result = $this->getPrimary()->send($message);
            $this->lastUsedService = $this->getPrimary();
            return $result;
        }
    }

    private function getPrimary(): Delivery\ServiceInterface
    {
        return di\Instance::ensure($this->primary, Delivery\ServiceInterface::class);
    }

    private function getFallback(): Delivery\ServiceInterface
    {
        return di\Instance::ensure($this->fallback, Delivery\ServiceInterface::class);
    }

    private function getCache(): caching\CacheInterface
    {
        return di\Instance::ensure($this->cache, caching\CacheInterface::class);
    }

    private function isPrimaryAvailable(): bool
    {
        return !$this->getCache()->get($this->getCacheKey());
    }

    private function markPrimaryAsFailed(): void
    {
        $this->getCache()->set(
            $this->getCacheKey(),
            true,
            $this->cacheDuration
        );
    }

    private function clearPrimaryFailedState(): void
    {
        $this->getCache()->delete($this->getCacheKey());
    }

    private function getCacheKey(): string
    {
        return $this->cacheKeyPrefix . $this->getPrimary()->name();
    }

    private function logException(Delivery\Exception $e): void
    {
        \Yii::error($e, $this->logCategory);
    }
}