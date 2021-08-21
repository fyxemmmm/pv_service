<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\JmbModel */

$this->title = 'Create Jmb Model';
$this->params['breadcrumbs'][] = ['label' => 'Jmb Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jmb-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
