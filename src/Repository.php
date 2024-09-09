<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2;

use Horat1us\Yii\Validation;
use Wearesho\Delivery;

class Repository implements Delivery\RepositoryInterface
{
    use Delivery\RepositoryTrait;

    public function getHistoryItem(Delivery\MessageInterface $message): ?Delivery\HistoryItemInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return HistoryItem::find()
            ->orderBy(['message_delivery_history.id' => SORT_DESC])
            ->andWhereMessage($message)
            ->one();
    }

    /**
     * @param Delivery\HistoryItemInterface $item
     * @throws Validation\Failure
     */
    public function save(Delivery\HistoryItemInterface $item): void
    {
        $record = new HistoryItem([
            'recipient' => $item->getRecipient(),
            'sent' => $item->isSent(),
            'text' => $item->getText(),
            'sender' => $item->getSender(),
        ]);

        if ($item instanceof Delivery\HistoryItemWithOptionsInterface) {
            $record->options = $item->getOptions();
        }

        Validation\Exception::saveOrThrow($record);
    }
}
