<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use Wearesho\Delivery;

class RepositoryTest extends Delivery\Yii2\Tests\TestCase
{
    use Delivery\Test\RepositoryTest;

    protected function getRepository(): Delivery\RepositoryInterface
    {
        return new Delivery\Yii2\Repository();
    }

    public function testPushWithOptions(): void
    {
        $options = [
            'foo' => 'bar',
            'bar' => 'foo',
        ];
        $message = new Delivery\MessageWithOptions(
            'text',
            '380930000000',
            $options
        );
        $sender = static::class;
        $isSent = true;

        $this->repository->push($message, $sender, $isSent);

        $this->assertEquals(
            $sender,
            $this->repository->getSender($message)
        );

        $this->assertEquals(
            $isSent,
            $this->repository->isSent($message)
        );

        $historyItem = $this->repository->getHistoryItem($message);

        $this->assertInstanceOf(
            Delivery\Yii2\HistoryItem::class,
            $historyItem
        );
        $this->assertEquals(
            $options,
            $historyItem->getOptions()
        );
    }
}
