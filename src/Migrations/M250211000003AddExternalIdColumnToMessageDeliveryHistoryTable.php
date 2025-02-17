<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\Migration;

class M250211000003AddExternalIdColumnToMessageDeliveryHistoryTable extends Migration
{
    private const TABLE_NAME = 'message_delivery_history';
    private const COLUMN_NAME = 'external_id';

    public function safeUp(): void
    {
        $this->addColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
            $this->text()->null()
        );
        $this->createIndex(
            $this->getIndexName(),
            static::TABLE_NAME,
            ['sender', 'external_id'],
            true
        );
    }

    public function safeDown(): void
    {
        $this->dropindex(
            $this->getIndexName(),
            static::TABLE_NAME,
        );
        $this->dropColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
        );
    }

    private function getIndexName(): string
    {
        return 'i_' . static::TABLE_NAME . '_' . static::COLUMN_NAME;
    }
}
