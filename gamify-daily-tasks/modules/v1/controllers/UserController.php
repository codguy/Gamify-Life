<?php

namespace app\modules\v1\controllers;

use Yii;
use app\models\User;
use app\models\form\LoginForm; // Will create this form model
use app\models\form\RegistrationForm; // Will create this form model
use yii\filters\VerbFilter;

class UserController extends ApiController // Extends our base ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Remove HttpBearerAuth for login and register actions
        $behaviors['authenticator']['except'] = ['login', 'register', 'options'];
        // Add verb filter
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'login'    => ['POST', 'OPTIONS'],
                'register' => ['POST', 'OPTIONS'],
                'me'       => ['GET', 'OPTIONS'],
                'logout'   => ['POST', 'OPTIONS'], // If implemented server-side
            ],
        ];
        return $behaviors;
    }

    /**
     * Handles user login and returns an access token.
     * @return array
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->login()) {
            $user = $model->getUser();
            $user->generateAccessToken();
            if ($user->save(false, ['access_token'])) { // Save only access_token
                return [
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                    ],
                    'access_token' => $user->access_token,
                ];
            } else {
                Yii::$app->response->statusCode = 500;
                return ['errors' => $user->getErrors()];
            }
        }

        Yii::$app->response->statusCode = 422; // Unprocessable Entity
        return ['errors' => $model->getErrors()];
    }

    /**
     * Handles user registration.
     * @return array
     */
    public function actionRegister()
    {
        $model = new RegistrationForm();
        if ($model->load(Yii::$app->request->getBodyParams(), '') && $user = $model->register()) {
            // User model's afterSave will create initial stats.
            // Login the user immediately and return token
            $user->generateAccessToken();
            if ($user->save(false, ['access_token'])) {
                Yii::$app->response->statusCode = 201; // Created
                return [
                    'message' => 'Registration successful. You are now logged in.',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                    ],
                    'access_token' => $user->access_token,
                ];
            } else {
                 Yii::$app->response->statusCode = 500;
                 return ['errors' => $user->getErrors()];
            }
        }

        Yii::$app->response->statusCode = 422; // Unprocessable Entity
        return ['errors' => $model->getErrors()];
    }

    /**
     * Returns details of the currently authenticated user.
     * @return User|null
     */
    public function actionMe()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        if ($user) {
            $statsData = [];
            // Eager load statCategory to avoid N+1 queries if accessing category name in a loop
            $userStats = $user->getUserStats()->with('statCategory')->all();

            foreach ($userStats as $userStat) {
                $statsData[] = [
                    'id' => $userStat->stat_category_id, // or $userStat->statCategory->id
                    'name' => $userStat->statCategory ? $userStat->statCategory->name : 'Unknown Category',
                    'value' => $userStat->value,
                    'description' => $userStat->statCategory ? $userStat->statCategory->description : null,
                ];
            }

            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'stats' => $statsData,
            ];
        }

        // This part should ideally not be reached if HttpBearerAuth is working correctly
        Yii::$app->response->statusCode = 401;
        return ['error' => 'User not authenticated.'];
    }

    /**
     * Logs out the current user by removing their access token.
     * @return array
     */
    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        if ($user instanceof User) {
            $user->removeAccessToken();
            if ($user->save(false, ['access_token'])) {
                return ['message' => 'Logout successful'];
            } else {
                Yii::$app->response->statusCode = 500;
                return ['errors' => $user->getErrors()];
            }
        }
        Yii::$app->response->statusCode = 401; // Or 200 if we want to silently succeed on no user
        return ['message' => 'Logout successful or no user was authenticated.'];
    }

    // OPTIONS requests are now handled by the CORS filter in ApiController
}
