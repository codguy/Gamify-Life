<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\User;
use app\models\StatsCategory;

/**
 * This is the model class for table "{{%user_stats}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $stat_category_id
 * @property int $value
 * @property string|null $updated_at (Handled by DB: ON UPDATE CURRENT_TIMESTAMP)
 *
 * @property StatsCategory $statCategory
 * @property User $user
 */
class UserStat extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_stats}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'stat_category_id'], 'required'],
            [['user_id', 'stat_category_id', 'value'], 'integer'],
            [['value'], 'default', 'value' => 0],
            // 'updated_at' is handled by the database, so no specific rule needed beyond 'safe' if it were mass assignable.
            // Gii's default 'safe' rule for 'updated_at' is acceptable if it's ever read from form input, though unlikely for this field.
            [['updated_at'], 'safe'],
            [['user_id', 'stat_category_id'], 'unique', 'targetAttribute' => ['user_id', 'stat_category_id']],
            [['stat_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => StatsCategory::class, 'targetAttribute' => ['stat_category_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'stat_category_id' => 'Stat Category ID',
            'value' => 'Value',
            'updated_at' => 'Updated At',
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
