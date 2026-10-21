<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Migrations;

use yii\db\Migration;
use yii\db\Query;

/**
 * External ID may be shared by messages sent within single bulk request (e.g. Termii),
 * so message is identified by external ID and recipient.
 *
 * PostgreSQL indexes are changed concurrently to not block writes,
 * which can not be done inside transaction, so up() and down() are used instead of safeUp() and safeDown().
 */
class M261021000000ReplaceExternalIdUniqueIndexMessageDeliveryHistoryTable extends Migration
{
    private const TABLE_NAME = 'message_delivery_history';
    private const OLD_INDEX_NAME = 'i_message_delivery_history_external_id';
    private const NEW_INDEX_NAME = 'i_message_delivery_history_sender_external_id_recipient';

    public function up()
    {
        try {
            if ($this->isPgsql()) {
                // index left invalid by failed concurrent build has to be removed before creating it again
                $this->dropIndexConcurrently(static::NEW_INDEX_NAME);
                $this->createIndexConcurrently(static::NEW_INDEX_NAME, ['sender', 'external_id', 'recipient']);
                $this->dropIndexConcurrently(static::OLD_INDEX_NAME);
            } else {
                $this->createIndex(
                    static::NEW_INDEX_NAME,
                    static::TABLE_NAME,
                    ['sender', 'external_id', 'recipient'],
                    true
                );
                $this->dropIndex(static::OLD_INDEX_NAME, static::TABLE_NAME);
            }
        } catch (\Throwable $e) {
            $this->printException($e);
            return false;
        }
        return null;
    }

    public function down()
    {
        try {
            $duplicate = (new Query())
                ->select(['sender', 'external_id'])
                ->from(static::TABLE_NAME)
                ->andWhere(['not', ['external_id' => null]])
                ->groupBy(['sender', 'external_id'])
                ->having('COUNT(*) > 1')
                ->one($this->db);
            if ($duplicate !== false) {
                throw new \RuntimeException(
                    "Unable to restore unique index on (sender, external_id): "
                    . "external ID {$duplicate['external_id']} of {$duplicate['sender']} is used by multiple messages."
                );
            }

            if ($this->isPgsql()) {
                $this->dropIndexConcurrently(static::OLD_INDEX_NAME);
                $this->createIndexConcurrently(static::OLD_INDEX_NAME, ['sender', 'external_id']);
                $this->dropIndexConcurrently(static::NEW_INDEX_NAME);
            } else {
                $this->createIndex(static::OLD_INDEX_NAME, static::TABLE_NAME, ['sender', 'external_id'], true);
                $this->dropIndex(static::NEW_INDEX_NAME, static::TABLE_NAME);
            }
        } catch (\Throwable $e) {
            $this->printException($e);
            return false;
        }
        return null;
    }

    private function isPgsql(): bool
    {
        return $this->db->driverName === 'pgsql';
    }

    private function createIndexConcurrently(string $name, array $columns): void
    {
        $this->execute(sprintf(
            'CREATE UNIQUE INDEX CONCURRENTLY %s ON %s (%s)',
            $this->db->quoteTableName($name),
            $this->db->quoteTableName(static::TABLE_NAME),
            implode(', ', array_map([$this->db, 'quoteColumnName'], $columns))
        ));
    }

    private function dropIndexConcurrently(string $name): void
    {
        $this->execute('DROP INDEX CONCURRENTLY IF EXISTS ' . $this->db->quoteTableName($name));
    }

    private function printException(\Throwable $e): void
    {
        echo 'Exception: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
        echo $e->getTraceAsString() . "\n";
    }
}
