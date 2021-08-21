<?php

namespace service\controllers;

use common\models\UserAttendancePriceModel;
use service\models\UserApplyPrice;
use yii\data\ActiveDataProvider;
use common\models\Common;


class UserAttendanceController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = UserAttendancePriceModel::find();  // 指定的签到奖励金额
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /*
     * 确认已经打钱
     * */
    public function actionEdit(){
        $price = $this->post('price');
        if(!$price || !is_numeric($price)) return Common::response(0, '请输入正确的金额');
        if($price > 10 || $price <= 0)  return Common::response(0, '输入金额区间有误, 范围 0~10 可以精确到分');
        $model = UserAttendancePriceModel::findOne(1);
        $model->attendance_price = $price;
        if($model->save()) return Common::response(1, '修改签到奖励额度成功');
        return Common::response(0, '修改签到奖励额度成功', $model->getErrors());
    }

}
