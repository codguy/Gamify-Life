<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\models\TaskStatImpact; // Already correct
use app\models\Task;
use app\models\UserStat;
use app\models\User;

/**
 * This is the model class for table "{{%stats_categories}}".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property TaskStatImpact[] $taskStatImpacts
 * @property Task[] $tasks
 * @property UserStat[] $userStats
 * @property User[] $users
 */
class StatsCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stats_categories}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            // removed default for description as it's nullable by default
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[TaskStatImpacts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskStatImpacts()
    {
        return $this->hasMany(TaskStatImpact::class, ['stat_category_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])->viaTable('{{%task_stat_impact}}', ['stat_category_id' => 'id']);
    }

    /**
     * Gets query for [[UserStats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserStats()
    {
        return $this->hasMany(UserStat::class, ['stat_category_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('{{%user_stats}}', ['stat_category_id' => 'id']);
    }
}
