<?php

namespace service\controllers;

use common\Bridge;
use common\models\UserModel;
use service\models\UserApplyPrice;
use yii\data\ActiveDataProvider;
use common\models\Common;

/*
 * 用户提现
 * */
class UserApplyPriceController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $status = $this->get('status');
        $query = UserApplyPrice::find()->andFilterWhere(['status' => $status])->orderBy('id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /*
     * 确认已经打钱
     * */
    public function actionConfirmRemitter(){
        $id = $this->post('id');
        $model = UserApplyPrice::find()->where(['id' =>$id, 'status' => Bridge::APPLY_DOING])->one(); // 只给处理中的打钱
        if(!empty($model)){
            $model->status = Bridge::APPLY_PASS;
            $model->update_time = date('Y-m-d H:i:s');
            if($model->save()) return Common::response(1, '已成功打款');
            return Common::response(0, '操作失败', $model->getErrors());
        }
        return Common::response(0, '操作失败 未找到对象');
    }

    /*
     * 由于订单信息(支付宝、银行卡)问题 驳回提现申请
     * */
    public function actionRejectApply(){
        $id = $this->post('id');
        $fail_reason = $this->post('fail_reason') ?? '';
        $model = UserApplyPrice::find()->where(['id' =>$id, 'status' => Bridge::APPLY_DOING])->one(); // 只给处理中的进行驳回申请
        if(!empty($model)){
            if(empty($fail_reason)) return Common::response(0, '请输入驳回理由');
            $transaction = UserApplyPrice::getDb()->beginTransaction();
            try {
                $model->status = Bridge::APPLY_REJECT; // 让用户提现失败的操作
                $model->update_time = date('Y-m-d H:i:s');
                $model->fail_reason = $fail_reason;
                $model->save();
                $user = UserModel::findOne($model->user_id);
                $user->available_money += $model->apply_price;
                $user->save();
                $transaction->commit();
                return Common::response(1, '驳回成功');
            } catch(\Exception $e) {
                $transaction->rollBack();
                return Common::response(0, '操作失败', $e->getMessage());
            } catch(\Throwable $e) {
                $transaction->rollBack();
                return Common::response(0, '操作失败', $e->getMessage());
            }
        }
        return Common::response(0, '操作失败 未找到对象');
    }

}
