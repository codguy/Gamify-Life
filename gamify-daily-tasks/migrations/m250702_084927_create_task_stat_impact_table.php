<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_stat_impact}}`.
 */
class m250702_084927_create_task_stat_impact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_stat_impact}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'stat_category_id' => $this->integer()->notNull(),
            'impact_value' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-task_stat_impact-task_id',
            '{{%task_stat_impact}}',
            'task_id',
            '{{%tasks}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-task_stat_impact-stat_category_id',
            '{{%task_stat_impact}}',
            'stat_category_id',
            '{{%stats_categories}}',
            'id',
            'CASCADE'
        );

        // Add unique constraint
        $this->createIndex(
            'idx-task_stat_impact-task_id-stat_category_id',
            '{{%task_stat_impact}}',
            ['task_id', 'stat_category_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_stat_impact}}');
    }
}
