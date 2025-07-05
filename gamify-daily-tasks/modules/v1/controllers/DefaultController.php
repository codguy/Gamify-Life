<?php

namespace app\modules\v1\controllers;

use yii\web\Controller;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        // For an API, you'd typically return data, e.g., JSON
        // return ['status' => 'success', 'message' => 'Welcome to API v1'];
        // For now, let's keep it simple or assume it might render a test view if one existed.
        // Since this is an API module, let's make it return a simple JSON response.
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'module' => 'v1',
            'status' => 'running',
            'message' => 'Welcome to the Gamify Life API v1!'
        ];
    }
}
