<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\JmbModel */

$this->title = 'Update Jmb Model: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Jmb Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="jmb-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
