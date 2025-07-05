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
 * @property string|null $auth_key     // For "remember me" and IdentityInterface
 * @property string|null $access_token // For API authentication
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
            [['auth_key'], 'string', 'max' => 32],
            [['access_token'], 'string', 'max' => 255], // Max length for access_token
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['access_token'], 'unique'],
            [['email'], 'email'],
            [['password'], 'string', 'min' => 6, 'on' => ['register', 'create']], // Password required on registration/create scenario
            [['password_hash'], 'required', 'except' => ['register', 'create']],
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
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
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
        // Note: Corrected class name from StatsCategories to StatsCategory if that's the actual class name.
        // Assuming StatsCategory is the correct class name based on previous model refinements.
        return $this->hasMany(StatsCategory::class, ['id' => 'stat_category_id'])->viaTable('{{%user_stats}}', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserStats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserStats()
    {
        return $this->hasMany(UserStat::class, ['user_id' => 'id']);
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
        return static::findOne(['access_token' => $token]);
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
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
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
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new access token
     * @throws \yii\base\Exception
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes access token
     */
    public function removeAccessToken()
    {
        $this->access_token = null;
    }

    /**
     * Before saving, hash password if it's a new record or password is changed.
     * Also generate auth_key for new records.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) { // For new records (isNewRecord is true)
                if (empty($this->auth_key)) {
                    $this->generateAuthKey();
                }
            }
            // Hash password if it's set (for new record or if password attribute changed)
            if (!empty($this->password)) {
                if ($this->isNewRecord || $this->isAttributeChanged('password_hash') || $this->getOldAttribute('password_hash') !== Yii::$app->security->generatePasswordHash($this->password, Yii::$app->params['passwordHashCost'] ?? null) ) { // A bit more robust check if password actually changed
                     $this->setPassword($this->password);
                }
            } elseif ($this->isNewRecord && empty($this->password_hash)) {
                // This should be caught by rules for 'register' or 'create' scenarios
                $this->addError('password', 'Password cannot be blank for a new user.');
                return false;
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
