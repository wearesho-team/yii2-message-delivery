<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\HistoryItem;

use yii\db;
use Wearesho\Delivery;

/**
 * @see \Wearesho\Delivery\Yii2\HistoryItem
 */
class Query extends db\ActiveQuery
{
    public function __construct(string $modelClass = Delivery\Yii2\HistoryItem::class, array $config = [])
    {
        parent::__construct($modelClass, $config);
    }

    public function andWhereMessage(Delivery\MessageInterface $message): self
    {
        return $this
            ->andWhere(['=', 'message_delivery_history.recipient', $message->getRecipient()])
            ->andWhere(['=', 'message_delivery_history.text', $message->getText()]);
    }
}
