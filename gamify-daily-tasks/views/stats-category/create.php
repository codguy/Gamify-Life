<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\StatsCategory $model */

$this->title = 'Create Stats Category';
$this->params['breadcrumbs'][] = ['label' => 'Stats Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stats-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
