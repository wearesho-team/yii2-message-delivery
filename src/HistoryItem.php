<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2;

use Carbon\Carbon;
use Horat1us\Yii\CarbonBehavior;
use Wearesho\Delivery;
use yii\db;

/**
 * Class HistoryItem
 * @package Wearesho\Delivery\Yii2
 *
 * @property string $id [integer]
 * @property string $sender [varchar(255)]
 * @property string $recipient [varchar(64)]
 * @property string $text
 * @property bool $sent [boolean]
 * @property int $created_at [timestamp(0)]
 */
class HistoryItem extends db\ActiveRecord implements Delivery\HistoryItemInterface
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
                'updatedAtAttribute' => null,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['recipient', 'text', 'sent', 'sender',], 'required',],
            [['sender',], 'string', 'max' => 255,],
            [['recipient',], 'string', 'max' => 64,],
            [['text',], 'string',],
            [['sent',], 'boolean',],
        ];
    }

    public function isSent(): bool
    {
        return (bool)$this->sent;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getSentAt(): \DateTimeInterface
    {
        return Carbon::parse($this->created_at);
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
