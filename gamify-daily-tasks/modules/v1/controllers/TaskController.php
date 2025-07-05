<?php

namespace app\modules\v1\controllers;

use Yii;
use app\models\Task;
use app\models\TaskSearch; // For actionIndex, if we adapt it for API
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

class TaskController extends ApiController // Extends our base ApiController
{
    public $modelClass = 'app\models\Task'; // Useful for Yii's built-in RESTful actions if we were to use them directly

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Verb filter specifies HTTP methods allowed for each action
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['GET', 'OPTIONS'],
                'view'   => ['GET', 'OPTIONS'],
                'create' => ['POST', 'OPTIONS'],
                'update' => ['PUT', 'PATCH', 'OPTIONS'],
                'delete' => ['DELETE', 'OPTIONS'],
            ],
        ];
        // Authenticator is inherited from ApiController, all actions require authentication
        return $behaviors;
    }

    /**
     * Lists all Task models for the authenticated user.
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        // Use TaskSearch model if complex filtering/sorting is needed,
        // otherwise, a simpler ActiveDataProvider setup.
        // For now, a simple list of user's tasks.
        $dataProvider = new ActiveDataProvider([
            'query' => Task::find()->where(['user_id' => Yii::$app->user->id]),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ]
        ]);
        return $dataProvider;
    }

    /**
     * Creates a new Task model.
     * @return Task|array
     */
    public function actionCreate()
    {
        $model = new Task();
        // user_id will be set by BlameableBehavior

        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->save()) {
            Yii::$app->response->statusCode = 201; // Created
            return $model;
        } elseif (!$model->hasErrors()) {
            // This handles cases where load() fails but there are no validation errors yet
            // (e.g. empty body for a POST request that requires data)
             Yii::$app->response->statusCode = 400; // Bad Request
             return ['errors' => 'Failed to load data or no data submitted.'];
        }

        Yii::$app->response->statusCode = 422; // Unprocessable Entity
        return ['errors' => $model->getErrors()];
    }

    /**
     * Displays a single Task model.
     * @param integer $id
     * @return Task|array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModelForCurrentUser($id);
        return $model;
    }

    /**
     * Updates an existing Task model.
     * @param integer $id
     * @return Task|array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModelForCurrentUser($id);

        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->save()) {
            return $model;
        } elseif (!$model->hasErrors()) {
             Yii::$app->response->statusCode = 400; // Bad Request
             return ['errors' => 'Failed to load data or no data submitted.'];
        }

        Yii::$app->response->statusCode = 422; // Unprocessable Entity
        return ['errors' => $model->getErrors()];
    }

    /**
     * Deletes an existing Task model.
     * @param integer $id
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModelForCurrentUser($id);
        if ($model->delete()) {
            Yii::$app->response->statusCode = 204; // No Content
            return []; // Return empty response on success
        }

        Yii::$app->response->statusCode = 500; // Internal Server Error
        return ['errors' => 'Failed to delete the task.'];
    }

    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Task::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException("Task with ID {$id} not found.");
    }

    /**
     * Finds the Task model and ensures it belongs to the current user.
     * @param integer $id
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model does not belong to the current user
     */
    protected function findModelForCurrentUser($id)
    {
        $model = $this->findModel($id);
        if ($model->user_id != Yii::$app->user->id) {
            throw new ForbiddenHttpException('You do not have permission to access this task.');
        }
        return $model;
    }

    // OPTIONS requests are now handled by the CORS filter in ApiController
}
