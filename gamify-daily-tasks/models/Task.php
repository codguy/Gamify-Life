<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use app\models\User; // Ensure User class is imported
use app\models\StatsCategory; // Ensure StatsCategory class is imported
use app\models\TaskStatImpact; // Ensure TaskStatImpact class is imported


/**
 * This is the model class for table "tasks".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string|null $due_date
 * @property string $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $completed_at
 *
 * @property StatsCategory[] $statCategories
 * @property TaskStatImpact[] $taskStatImpacts
 * @property User $user
 */
class Task extends ActiveRecord // Use ActiveRecord alias
{
    /**
     * ENUM field values
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tasks}}'; // Use table prefix
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                // if you don't want to use UNIX timestamp, set value to an expression
                // 'value' => new \yii\db\Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false, // We don't have an updated_by user_id field
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'], // user_id will be handled by BlameableBehavior
            [['description', 'status'], 'string'],
            [['due_date', 'completed_at'], 'safe'], // created_at and updated_at handled by TimestampBehavior
            [['title'], 'string', 'max' => 255],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED, self::STATUS_CANCELLED]],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            // user_id validation is implicitly handled by BlameableBehavior and DB foreign key
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'description' => 'Description',
            'due_date' => 'Due Date',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'completed_at' => 'Completed At',
        ];
    }

    /**
     * Gets query for [[StatCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatCategories()
    {
        return $this->hasMany(StatsCategories::class, ['id' => 'stat_category_id'])->viaTable('task_stat_impact', ['task_id' => 'id']);
    }

    /**
     * Gets query for [[TaskStatImpacts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskStatImpacts()
    {
        return $this->hasMany(TaskStatImpact::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }


    /**
     * Returns the list of status options.
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Gets the status label.
     * @return string
     */
    public function getStatusLabel()
    {
        $options = self::getStatusOptions();
        return isset($options[$this->status]) ? $options[$this->status] : '';
    }

    /**
     * {@inheritdoc}
     * After saving, if the task status was changed to 'completed', update user stats.
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Check if 'status' was changed and the new status is 'completed'
        if (array_key_exists('status', $changedAttributes) && $this->status === self::STATUS_COMPLETED) {
            // Also handle if it's a new record and status is directly set to completed
            $is_newly_completed = true;
        } elseif ($insert && $this->status === self::STATUS_COMPLETED) {
            $is_newly_completed = true;
        } else {
            $is_newly_completed = false;
        }

        // Ensure it wasn't already completed and being re-saved with completed status
        // (unless specific logic for re-completion with effects is desired - not for now)
        if (isset($changedAttributes['status']) && $changedAttributes['status'] === self::STATUS_COMPLETED && $this->status === self::STATUS_COMPLETED) {
            $is_newly_completed = false; // Status was already completed, no new impact
        }


        if ($is_newly_completed) {
            if ($this->status === self::STATUS_COMPLETED && (!isset($changedAttributes['status']) || $changedAttributes['status'] !== self::STATUS_COMPLETED ) ) {
                if (empty($this->completed_at)) {
                    $this->completed_at = date('Y-m-d H:i:s'); // Set completion time
                    $this->save(false, ['completed_at']); // Save only completed_at without re-triggering full save cycle
                }
            }

            $impacts = $this->getTaskStatImpacts()->all();
            if ($this->user_id) { // Ensure user_id is set
                foreach ($impacts as $impact) {
                    $userStat = UserStat::findOne([
                        'user_id' => $this->user_id,
                        'stat_category_id' => $impact->stat_category_id,
                    ]);

                    if ($userStat) {
                        $userStat->value += $impact->impact_value;
                        if (!$userStat->save()) {
                            Yii::error("Failed to update UserStat for user_id {$this->user_id}, stat_category_id {$impact->stat_category_id}: " . print_r($userStat->getErrors(), true));
                        }
                    } else {
                        // This case should ideally not happen if User->afterSave correctly initializes all stats.
                        // But as a fallback, create it.
                        $newUserStat = new UserStat([
                            'user_id' => $this->user_id,
                            'stat_category_id' => $impact->stat_category_id,
                            'value' => $impact->impact_value, // Initial value is the impact itself
                        ]);
                        if (!$newUserStat->save()) {
                             Yii::error("Failed to create UserStat for user_id {$this->user_id}, stat_category_id {$impact->stat_category_id}: " . print_r($newUserStat->getErrors(), true));
                        }
                    }
                }
            }
        } elseif (isset($changedAttributes['status']) &&
                   $changedAttributes['status'] === self::STATUS_COMPLETED &&
                   $this->status !== self::STATUS_COMPLETED) {
            // Task was 'completed' but is now something else (e.g., 'pending' - re-opened)
            // Implement logic to revert stat changes if necessary (optional - can be complex)
            // For now, we only add points on completion, no subtraction on re-opening.
            // Also, clear completed_at if task is no longer completed
             if ($this->status !== self::STATUS_COMPLETED && !empty($this->completed_at)) {
                $this->completed_at = null;
                $this->save(false, ['completed_at']);
            }
        }
    }
}
