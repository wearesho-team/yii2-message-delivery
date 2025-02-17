<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2;

use Horat1us\Yii\CarbonBehavior;
use Carbon\Carbon;
use Wearesho\Delivery;
use yii\db;

/**
 * Class HistoryItem
 * @package Wearesho\Delivery\Yii2
 *
 * @property string $id [integer]
 * @property string $sender [varchar(255)]
 * @property string $recipient [varchar(64)]
 * @property string $text [text]
 * @property string $created_at [timestamp(0)]
 * @property string $updated_at [timestamp(0)]
 * @property array|null $options [json]
 * @property string $status [enum]
 * @property string|null $reason [text]
 * @property string|null $external_id
 */
class HistoryItem extends db\ActiveRecord
{
    final public static function tableName(): string
    {
        return 'message_delivery_history';
    }

    public static function find(): Delivery\Yii2\HistoryItem\Query
    {
        return new Delivery\Yii2\HistoryItem\Query();
    }

    public function behaviors(): array
    {
        return [
            'ts' => [
                'class' => CarbonBehavior::class,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['recipient', 'text', 'status', 'sender',], 'required',],
            [['sender', 'external_id', 'reason',], 'string', 'max' => 255,],
            [['recipient',], 'string', 'max' => 64,],
            [['text',], 'string',],
            [['status',], 'in', 'range' => fn() => array_map(
                fn(Delivery\Result\Status $status) => $status->value,
                Delivery\Result\Status::cases()
            ),],
            [['options',], 'validateOptions',],
        ];
    }

    /**
     * Validates the options attribute to ensure it's either an array or null.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateOptions($attribute, $params): void
    {
        if (!is_array($this->$attribute) && $this->$attribute !== null) {
            $this->addError($attribute, 'The options must be an array or null.');
        }
    }

    public function toItem(): Delivery\History\ItemInterface
    {
        $message = new Delivery\Message(
            text: $this->text,
            recipient: $this->recipient,
            options: $this->options ?? []
        );
        $result = new Delivery\Result(
            messageId: $this->external_id,
            message: $message,
            status: Delivery\Result\Status::from($this->status),
            reason: $this->reason,
        );
        return new Delivery\History\Item(
            id: (int)$this->id,
            result: $result,
            serviceName: $this->sender,
            at: Carbon::parse($this->created_at),
            updatedAt: Carbon::parse($this->updated_at),
        );
    }
}
