<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_stats}}`.
 */
class m250702_084926_create_user_stats_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_stats}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'stat_category_id' => $this->integer()->notNull(),
            'value' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-user_stats-user_id',
            '{{%user_stats}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-user_stats-stat_category_id',
            '{{%user_stats}}',
            'stat_category_id',
            '{{%stats_categories}}',
            'id',
            'CASCADE'
        );

        // Add unique constraint
        $this->createIndex(
            'idx-user_stats-user_id-stat_category_id',
            '{{%user_stats}}',
            ['user_id', 'stat_category_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_stats}}');
    }
}
