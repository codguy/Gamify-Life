<?php

namespace app\modules\v1;

/**
 * v1 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\v1\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        // For an API module, you might want to set Yii::$app->user->enableSession to false
        // if you are using token-based authentication.
        \Yii::$app->user->enableSession = false;
    }
}
