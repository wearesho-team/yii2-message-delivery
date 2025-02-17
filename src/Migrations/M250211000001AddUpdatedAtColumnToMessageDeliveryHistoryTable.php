<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\Migration;

class M250211000001AddUpdatedAtColumnToMessageDeliveryHistoryTable extends Migration
{
    private const TABLE_NAME = 'message_delivery_history';
    private const COLUMN_NAME = 'updated_at';

    public function safeUp(): void
    {
        $timestamp = $this->timestamp();
        if ($this->db->driverName === 'mysql') {
            $timestamp->defaultExpression('now()');
        }
        $this->addColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
            $timestamp
        );
        $this->execute(<<<SQL
UPDATE message_delivery_history SET updated_at=created_at
SQL
        );
        $this->alterColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
            $timestamp->notNull(),
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
        );
    }
}
