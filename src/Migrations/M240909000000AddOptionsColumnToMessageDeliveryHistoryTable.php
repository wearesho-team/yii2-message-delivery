<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\Migration;

class M240909000000AddOptionsColumnToMessageDeliveryHistoryTable extends Migration
{
    private const TABLE_NAME = 'message_delivery_history';
    private const COLUMN_NAME = 'options';

    public function safeUp(): void
    {
        $this->addColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
            $this->json()->null()
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn(static::TABLE_NAME, static::COLUMN_NAME);
    }
}
