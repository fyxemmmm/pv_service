<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\JmbModel */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Jmb Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="jmb-model-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'brand_name',
            'desc',
            'direct_store_num',
            'join_store_num',
            'apply_num',
            'main_project',
            'register_time',
            'location',
            'est_init_investment',
            'est_customer_unit_price',
            'est_customer_daily_flow',
            'est_mothly_sale',
            'est_gross_profit',
            'est_payback_period',
            'inital_fee',
            'join_fee',
            'deposit_fee',
            'device_fee',
            'other_fee',
            'info:ntext',
            'create_time',
            'update_time',
        ],
    ]) ?>

</div>
