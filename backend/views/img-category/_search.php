<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\JmbModelSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jmb-model-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'brand_name') ?>

    <?= $form->field($model, 'desc') ?>

    <?= $form->field($model, 'direct_store_num') ?>

    <?php // echo $form->field($model, 'join_store_num') ?>

    <?php // echo $form->field($model, 'apply_num') ?>

    <?php // echo $form->field($model, 'main_project') ?>

    <?php // echo $form->field($model, 'register_time') ?>

    <?php // echo $form->field($model, 'location') ?>

    <?php // echo $form->field($model, 'est_init_investment') ?>

    <?php // echo $form->field($model, 'est_customer_unit_price') ?>

    <?php // echo $form->field($model, 'est_customer_daily_flow') ?>

    <?php // echo $form->field($model, 'est_mothly_sale') ?>

    <?php // echo $form->field($model, 'est_gross_profit') ?>

    <?php // echo $form->field($model, 'est_payback_period') ?>

    <?php // echo $form->field($model, 'inital_fee') ?>

    <?php // echo $form->field($model, 'join_fee') ?>

    <?php // echo $form->field($model, 'deposit_fee') ?>

    <?php // echo $form->field($model, 'device_fee') ?>

    <?php // echo $form->field($model, 'other_fee') ?>

    <?php // echo $form->field($model, 'info') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'update_time') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
