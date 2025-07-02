<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Task; // Added to use getStatusOptions()

/** @var yii\web\View $this */
/** @var app\models\Task $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="task-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'due_date')->textInput(['type' => 'date']) // Changed to date type for better UX ?>

    <?= $form->field($model, 'status')->dropDownList(Task::getStatusOptions(), ['prompt' => 'Select Status']) ?>

    <?= $form->field($model, 'completed_at')->textInput(['type' => 'date']) // Changed to date type for better UX ?>
    <?php // user_id is set by BlameableBehavior ?>
    <?php // created_at and updated_at are set by TimestampBehavior ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
