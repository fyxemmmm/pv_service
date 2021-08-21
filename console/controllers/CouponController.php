<?php
namespace console\controllers;

use yii\console\Controller;
use api\models\User;
use common\models\HuanXin;
use common\models\CouponModel;
use common\models\CouponUserModel;
use Yii;

class CouponController extends Controller
{

    public $message;

    public function options($actionID)
    {
        return ['message'];
    }

    public function optionAliases()
    {
        return ['m' => 'message'];
    }

    public function actionIndex()
    {
        $time = date('Y-m-d H:i:s');
        //$model = CouponUserModel::find()->where(['coupon_status'=>0])->andwhere(['<','end_time',$time])->all();
        CouponUserModel::updateAll(['coupon_status'=>2],['and', ['coupon_status' => 0],['<','end_time',$time]]);
        return 'SUCCESS!';
    }
}