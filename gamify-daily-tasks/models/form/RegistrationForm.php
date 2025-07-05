<?php

namespace app\models\form;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * RegistrationForm is the model behind the registration form.
 */
class RegistrationForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_repeat;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'password_repeat'], 'required'],
            ['username', 'trim'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match."],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Repeat Password',
        ];
    }

    /**
     * Registers a new user.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function register()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        // The $user->password virtual attribute will be used by User::beforeSave to setPassword()
        $user->password = $this->password;

        // User::beforeSave will also generate auth_key
        // User::afterSave will create initial UserStats

        if ($user->save()) {
            return $user;
        } else {
            // Transfer errors from User model to RegistrationForm model if specific fields fail
            // This helps in showing DB-level validation errors (like unique constraints if somehow bypassed client-side)
            // For now, we rely on User model save errors being caught by controller if any.
            // A more robust way would be to explicitly add User model errors to this form model.
            Yii::error("User model save errors: " . print_r($user->getErrors(), true));
            return null;
        }
    }
}
