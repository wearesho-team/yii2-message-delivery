<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests;

use Wearesho\Delivery;

/**
 * Records sent messages and returns accepted results with sequential message IDs.
 */
class ServiceMock implements Delivery\Batch\ServiceInterface
{
    /** @var Delivery\MessageInterface[] */
    public array $messages = [];

    public int $batchCount = 0;

    /** Thrown on sending and balance check when set. */
    public ?Delivery\Exception $exception = null;

    public function __construct(
        private readonly string $name = 'mock',
        private readonly float $balance = 0,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function balance(): Delivery\BalanceInterface
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }
        return new Delivery\Balance($this->balance, 'UAH');
    }

    public function send(Delivery\MessageInterface $message): Delivery\ResultInterface
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }
        $this->messages[] = $message;

        return new Delivery\Result(
            messageId: $this->name . '_' . count($this->messages),
            message: $message,
            status: Delivery\Result\Status::Accepted,
        );
    }

    public function batch(iterable $messages): iterable
    {
        $this->batchCount++;
        foreach ($messages as $message) {
            yield $this->send($message);
        }
    }
}
