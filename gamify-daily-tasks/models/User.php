<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\Task; // Corrected from Tasks
use app\models\UserStat; // Corrected from UserStats
use app\models\StatsCategory; // Corrected from StatsCategories

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string|null $auth_key // Added for IdentityInterface
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property StatsCategory[] $statCategories
 * @property Task[] $tasks
 * @property UserStat[] $userStats
 */
class User extends ActiveRecord implements IdentityInterface
{
    public $password; // Virtual property for password input

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                // attributes are already integers in the DB from previous change to Task
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [['username', 'email'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['password'], 'string', 'min' => 6, 'on' => 'register'], // Password required on registration scenario
            [['password_hash'], 'required', 'except' => 'register'], // hash required unless it's registration scenario
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'email' => 'Email',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[StatCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatCategories()
    {
        return $this->hasMany(StatsCategories::class, ['id' => 'stat_category_id'])->viaTable('user_stats', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Tasks::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserStats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserStats()
    {
        return $this->hasMany(UserStats::class, ['user_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        // If you add an 'auth_key' column to your users table:
        // return $this->auth_key;
        return null; // Or implement auth_key generation and storage
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        // If you add an 'auth_key' column to your users table:
        // return $this->getAuthKey() === $authKey;
        return true; // Modify if using auth_key
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     * (Call this method when a user is created or auth_key needs to be regenerated)
     */
    public function generateAuthKey()
    {
        // If you add an 'auth_key' column to your users table:
        // $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Before saving, hash password if it's a new record or password is changed.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord || $this->isAttributeChanged('password')) {
                 if (!empty($this->password)) { // Only hash if password is set
                    $this->setPassword($this->password);
                 } elseif ($this->isNewRecord && empty($this->password_hash)) {
                    // This case should ideally be caught by validation (e.g. password required on register)
                    // For safety, prevent saving a new user without any password info.
                    $this->addError('password', 'Password cannot be blank for a new user.');
                    return false;
                 }
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * After saving a new user, create their initial stats.
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            // New user, create initial stats
            $statCategories = StatsCategory::find()->all();
            foreach ($statCategories as $category) {
                $userStat = new UserStat();
                $userStat->user_id = $this->id;
                $userStat->stat_category_id = $category->id;
                $userStat->value = 0; // Initial value, can be configured later
                if (!$userStat->save()) {
                    // Log error or handle - for now, basic error logging
                    Yii::error("Failed to save UserStat for user_id {$this->id} and stat_category_id {$category->id}: " . print_r($userStat->getErrors(), true));
                }
            }
        }
    }
}
