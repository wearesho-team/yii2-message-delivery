<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\Migration;

class M250226000001AddRecipientIndexMessageDeliveryHistoryTable extends Migration
{
    private const TABLE_NAME = 'message_delivery_history';
    private const COLUMN_NAME = 'recipient';

    public function safeUp(): void
    {
        $this->createIndex(
            $this->getIndexName(),
            static::TABLE_NAME,
            ['recipient'],
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex(
            $this->getIndexName(),
            static::TABLE_NAME,
        );
    }

    private function getIndexName(): string
    {
        return 'i_' . static::TABLE_NAME . '_' . static::COLUMN_NAME;
    }
}
