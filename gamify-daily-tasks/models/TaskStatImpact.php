<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\Task;
use app\models\StatsCategory;

/**
 * This is the model class for table "{{%task_stat_impact}}".
 *
 * @property int $id
 * @property int $task_id
 * @property int $stat_category_id
 * @property int $impact_value
 *
 * @property StatsCategory $statCategory
 * @property Task $task
 */
class TaskStatImpact extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%task_stat_impact}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id', 'stat_category_id', 'impact_value'], 'required'],
            [['task_id', 'stat_category_id', 'impact_value'], 'integer'],
            [['task_id', 'stat_category_id'], 'unique', 'targetAttribute' => ['task_id', 'stat_category_id']],
            [['stat_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => StatsCategory::class, 'targetAttribute' => ['stat_category_id' => 'id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'stat_category_id' => 'Stat Category ID',
            'impact_value' => 'Impact Value',
        ];
    }

    /**
     * Gets query for [[StatCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatCategory()
    {
        return $this->hasOne(StatsCategory::class, ['id' => 'stat_category_id']);
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
