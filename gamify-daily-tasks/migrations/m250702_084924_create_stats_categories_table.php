<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stats_categories}}`.
 */
class m250702_084924_create_stats_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stats_categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'description' => $this->text(),
        ]);

        // Seed initial categories
        $this->batchInsert('{{%stats_categories}}', ['name', 'description'], [
            ['Physical Health', 'Overall physical well-being, fitness, and energy levels.'],
            ['Creativity', 'Imagination, artistic expression, and innovative thinking.'],
            ['Knowledge', 'Learning, understanding, and acquiring new skills or information.'],
            ['Happiness', 'Emotional well-being, joy, and life satisfaction.'],
            ['Money', 'Financial resources, wealth management, and economic stability.'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stats_categories}}');
    }
}
