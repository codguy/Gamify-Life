<?php

namespace app\modules\v1\controllers;

use Yii;
use app\models\StatsCategory;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;

class StatsCategoryController extends ApiController // Extends our base ApiController
{
    public $modelClass = 'app\models\StatsCategory';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Verb filter specifies HTTP methods allowed for each action
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['GET', 'OPTIONS'],
                // Add other actions here if CRUD is needed later (e.g., create, update, delete)
                // For now, only 'index' to list categories.
            ],
        ];
        // Authenticator is inherited from ApiController.
        // If this endpoint should be public, 'index' action can be added to 'except' list here:
        // $behaviors['authenticator']['except'] = ['index', 'options'];
        return $behaviors;
    }

    /**
     * Lists all StatsCategory models.
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => StatsCategory::find(),
            // No pagination needed if the list is always short, otherwise configure:
            /*
            'pagination' => [
                'pageSize' => 10, // Example page size
            ],
            */
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ]
            ]
        ]);
        return $dataProvider;
    }

    // OPTIONS requests are now handled by the CORS filter in ApiController
}
