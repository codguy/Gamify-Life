<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\StatsCategory $model */

$this->title = 'Update Stats Category: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stats Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stats-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
