<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\JmbModelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Jmb Models';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jmb-model-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Jmb Model', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'brand_name',
            'desc',
            'direct_store_num',
            //'join_store_num',
            //'apply_num',
            //'main_project',
            //'register_time',
            //'location',
            //'est_init_investment',
            //'est_customer_unit_price',
            //'est_customer_daily_flow',
            //'est_mothly_sale',
            //'est_gross_profit',
            //'est_payback_period',
            //'inital_fee',
            //'join_fee',
            //'deposit_fee',
            //'device_fee',
            //'other_fee',
            //'info:ntext',
            //'create_time',
            //'update_time',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
