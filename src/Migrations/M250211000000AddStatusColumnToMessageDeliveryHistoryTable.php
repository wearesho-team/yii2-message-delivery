<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\ColumnSchemaBuilder;
use yii\db\Migration;

class M250211000000AddStatusColumnToMessageDeliveryHistoryTable extends Migration
{
    private const TABLE_NAME = 'message_delivery_history';
    private const COLUMN_NAME = 'status';
    private const SENT_COLUMN_NAME = 'sent';
    private const ENUM_NAME = 'message_delivery_history_status';

    public function safeUp(): void
    {
        $columnType = $this->columnType();

        $this->addColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
            $this->columnType(),
        );

        $this->execute(<<<SQL
UPDATE message_delivery_history
SET status=(CASE WHEN is_sent IS TRUE THEN 'Sent' ELSE 'Failed' END)
SQL
        );

        $this->alterColumn(
            static::TABLE_NAME,
            static::COLUMN_NAME,
            $columnType->notNull(),
        );
        $this->dropColumn(
            static::TABLE_NAME,
            static::SENT_COLUMN_NAME,
        );
    }

    public function safeDown(): void
    {
        $this->addColumn(
            static::TABLE_NAME,
            static::SENT_COLUMN_NAME,
            $this->boolean(),
        );
        $this->execute(<<<SQL
UPDATE message_delivery_history
SET is_sent=(status='Delivered' OR status='Sent' OR status='Accepted' OR status='Read' OR status='Queued')
SQL
        );
        $this->alterColumn(
            static::TABLE_NAME,
            static::SENT_COLUMN_NAME,
            $this->boolean()->notNull(),
        );
        $this->dropColumn(static::TABLE_NAME, static::COLUMN_NAME);
        $this->dropType(static::ENUM_NAME);
    }

    private function columnType(): ColumnSchemaBuilder
    {
        if ($this->db->driverName === 'mysql') {
            return $this->string(32);
        } else {
            return $this->enum(static::ENUM_NAME, [
                'Queued',
                'Accepted',
                'Sent',
                'Delivered',
                'Read',
                'Expired',
                'Undelivered',
                'Rejected',
                'Unknown',
                'Failed',
                'Cancelled',
                'Error',
            ]);
        }
    }

    private function enum(string $name, array $values)
    {
        $this->dropType($name, true);

        $valuesString = implode(',', array_map(function (string $value) {
            $value = \pg_escape_string($value);
            return "'$value'";
        }, $values));

        echo "    > create type enum $name ($valuesString) ...";
        $time = microtime(true);
        $this->getDb()
            ->createCommand("CREATE TYPE {$name} AS ENUM ({$valuesString});")
            ->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $this->getDb()->getSchema()->createColumnSchemaBuilder($name);
    }

    private function dropType(string $type, bool $skipMissingType = false)
    {
        echo "    > drop type $type ...";
        $time = microtime(true);
        $skipMissingType = $skipMissingType ? " IF EXISTS" : "";
        $this->db->createCommand("DROP TYPE{$skipMissingType} {$type};")->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }
}
