<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2;

use Carbon\Carbon;
use Horat1us\Yii\Validation;
use Wearesho\Delivery;
use Wearesho\Delivery\History\ItemInterface;

class Repository implements Delivery\History\RepositoryInterface
{
    public function add(string $serviceName, Delivery\ResultInterface $item): ItemInterface
    {
        $historyItem = new HistoryItem();
        $historyItem->setAttributes(
            $this->getAttributes($serviceName, $item)
        );

        Validation\Exception::saveOrThrow($historyItem);

        return $historyItem->toItem();
    }

    public function batch(string $serviceName, array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $attributes = array_keys(
            $this->getAttributes($serviceName, $items[array_key_first($items)])
        );
        $results = [];
        $insertedCount = HistoryItem::getDb()->createCommand()->batchInsert(
            HistoryItem::tableName(),
            array_merge($attributes, ['created_at', 'updated_at']),
            array_map(
                function (Delivery\ResultInterface $result) use ($serviceName, &$results): array {
                    $attributes = $this->getAttributes($serviceName, $result);
                    $historyItem = new HistoryItem();
                    $historyItem->setAttributes($attributes);
                    $historyItem->created_at = Carbon::now()->toDateTimeString();
                    $historyItem->updated_at = $historyItem->created_at;
                    $results[] = $historyItem->toItem();
                    if (!empty($attributes['options'])) {
                        $attributes['options'] = json_encode($attributes['options'], JSON_THROW_ON_ERROR);
                    }
                    $attributes['created_at'] = $historyItem->created_at;
                    $attributes['updated_at'] = $historyItem->updated_at;
                    return $attributes;
                },
                $items
            )
        )->execute();

        return ($insertedCount === count($results)) ? $results : [];
    }

    private function getAttributes(string $serviceName, Delivery\ResultInterface $item): array
    {
        $message = $item->message();
        $options = $message->getOptions();

        return [
            'sender' => $serviceName,
            'recipient' => $message->getRecipient(),
            'text' => $message->getText(),
            'options' => empty($options) ? null : $options,
            'status' => $item->status()->value,
            'reason' => $item->reason(),
            'external_id' => $item->messageId(),
        ];
    }

    public function update(
        ItemInterface $item,
        Delivery\ResultInterface $result,
        ?string $serviceName = null
    ): ItemInterface {
        $historyItem = HistoryItem::find()
            ->andWhere(['=', 'id', $item->id()])
            ->one();

        if (!$historyItem instanceof HistoryItem) {
            throw new \InvalidArgumentException("Unable to find history item {$item->id()}");
        }

        $attributes = $this->getAttributes($serviceName ?? $item->serviceName(), $result);
        $historyItem->setAttributes($attributes);

        Validation\Exception::saveOrThrow($historyItem);

        return $historyItem->toItem();
    }

    public function getById(int $id): ?ItemInterface
    {
        /** @var HistoryItem|null $historyItem */
        $historyItem = HistoryItem::find()
            ->andWhere(['=', 'id', $id])
            ->one();

        return $historyItem?->toItem();
    }

    public function getByResultId(string $serviceName, string $resultId): ?ItemInterface
    {
        /** @var HistoryItem|null $historyItem */
        $historyItem = HistoryItem::find()
            ->andWhere(['=', 'external_id', $resultId])
            ->andWhere(['=', 'sender', $serviceName])
            ->one();

        return $historyItem?->toItem();
    }
}
