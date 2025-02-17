<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\Migration;

class M250211000002AddReasonColumnToMessageDeliveryHistoryTable extends Migration
{
    private const TABLE_NAME = 'message_delivery_history';
    private const COLUMN_NAME = 'reason';

    public function safeUp(): void
    {
        $this->addColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
            $this->text()->null()
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
