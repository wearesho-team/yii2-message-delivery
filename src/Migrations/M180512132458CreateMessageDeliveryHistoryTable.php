<?php

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\Migration;

/**
 * Class M180512132458CreateMessageDeliveryHistoryTable
 */
class M180512132458CreateMessageDeliveryHistoryTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $timestamp = $this->timestamp()->notNull();
        if ($this->db->driverName === 'mysql') {
            $timestamp->defaultExpression('now()');
        }

        $this->createTable(
            'message_delivery_history',
            [
                'id' => $this->primaryKey(),
                'sender' => $this->string()->notNull(),
                'recipient' => $this->string(64)->notNull(),
                'text' => $this->text()->notNull(),
                'sent' => $this->boolean()->notNull(),
                'created_at' => $timestamp,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('message_delivery_history');
    }
}
