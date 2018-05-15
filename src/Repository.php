<?php

namespace Wearesho\Delivery\Yii2;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Delivery;

/**
 * Class Repository
 * @package Wearesho\Delivery\Yii2
 */
class Repository implements Delivery\RepositoryInterface
{
    use Delivery\RepositoryTrait;

    public function getHistoryItem(Delivery\MessageInterface $message): ?Delivery\HistoryItemInterface
    {
        return HistoryItem::find()
            ->orderBy(['message_delivery_history.id' => SORT_DESC])
            ->andWhereMessage($message)
            ->one();
    }

    /**
     * @param Delivery\HistoryItemInterface $item
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function save(Delivery\HistoryItemInterface $item): void
    {
        $record = new HistoryItem([
            'recipient' => $item->getRecipient(),
            'sent' => $item->isSent(),
            'text' => $item->getText(),
            'sender' => $item->getSender(),
        ]);

        ModelException::saveOrThrow($record);
    }
}
