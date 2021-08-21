<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\JmbModel */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jmb-model-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'brand_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'desc')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'direct_store_num')->textInput() ?>

    <?= $form->field($model, 'join_store_num')->textInput() ?>

    <?= $form->field($model, 'apply_num')->textInput() ?>

    <?= $form->field($model, 'main_project')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'register_time')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'est_init_investment')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'est_customer_unit_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'est_customer_daily_flow')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'est_mothly_sale')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'est_gross_profit')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'est_payback_period')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'inital_fee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'join_fee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'deposit_fee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'device_fee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'other_fee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'info')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'create_time')->textInput() ?>

    <?= $form->field($model, 'update_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
