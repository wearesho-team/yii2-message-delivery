<?php

namespace Wearesho\Delivery\Yii2;

use Wearesho\Delivery;

/**
 * Class MessageWithSender
 * @package Wearesho\Delivery\Yii2
 */
class MessageWithSender extends Delivery\Message implements Delivery\ContainsSenderName
{
    /** @var string */
    protected $senderName;

    public function __construct(string $text, string $recipient, string $senderName)
    {
        parent::__construct($text, $recipient);
        $this->senderName = $senderName;
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }
}
