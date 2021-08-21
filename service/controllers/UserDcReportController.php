<?php

namespace service\controllers;

use common\Bridge;
use common\Config;
use common\Helper;
use common\models\UserDcOrderModel;
use common\models\UserIncomeModel;
use common\models\UserModel;
use yii\data\ActiveDataProvider;
use common\models\UserDcReportModel;
use common\models\Common;

class UserDcReportController extends CommonController
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
        $query = UserDcReportModel::find()->orderBy('id desc')->andFilterWhere(['status' => $status])->orderBy('id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $query = UserDcReportModel::find()->where(['id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /*
     * 驳回报备请求
     * */
    public function actionReject(){
        $id = $this->post('id');
        $fail_reason = $this->post('fail_reason') ?? '';
        if (empty($fail_reason)) return Common::response(0, '请输入驳回理由');

        $model = UserDcReportModel::find()->where(['id' =>$id, 'status' => Config::REPORT_DOING])->one(); // 只给申请中的进行驳回申请
        if(!empty($model)) {
            $model->fail_reason = $fail_reason;
            $model->status = Config::REPORT_FAIL;
            if($model->save()) return Common::response(1, '驳回成功');
            return Common::response(0, '操作失败', $model->getErrors());
        }
        return Common::response(0, '操作失败 未找到对象');
    }


    /*
     * 报备审核成功 进行打款
     * */
    public function actionPass(){
        $id = $this->post('id');
        $model = UserDcReportModel::find()->where(['id' =>$id, 'status' => Config::REPORT_DOING])->one(); // 只给申请中的进行报备成功
        if(!$model) return Common::response(0, '未找到对象');
        $transaction = UserDcReportModel::getDb()->beginTransaction();
        $bridge = new Bridge();
        $award = $bridge->fetchFirstAward($model->product_id);
        if(isset($award['message'])) return Common::response(0, $award['message']);
        try {
            $model->status = Config::REPORT_SUCCESS;
            $model->save();

            $user = UserModel::find()->where(['id' => $model->leader_id])->one();
            $user->total_income += $model->return_money;
            $user->available_money += $model->return_money;
            $user->save();

            // 判断用户是否是首次下款,如果是则返回相应的佣金
            $first_buy = UserDcOrderModel::find()->where(['leader_id' => $model->leader_id, 'user_id' => $model->user_id, 'status'=>Bridge::AUDIT_PASS])->one(); // 查询是否是第一次下款
            if(!$first_buy){ // 如果没有下过款,说明这是第一次下款
                $income_model = new UserIncomeModel();
                $income_model->user_id = $model->leader_id;
                $income_model->type = Bridge::INCOME_TYPE_FIRST_AWARD;
                $income_model->order_sign = Common::genOrderSign();
                $income_model->income = $award;
                $income_model->create_time = date('Y-m-d H:i:s');
                $income_model->save();

                $user->available_money += $award;
                $user->total_income += $award;
                $user->save();
                Helper::pushMessage(5,['accept_user_id' => $model->leader_id,'content' => Config::INCOMEMSG]);
            }

            $order_model = new UserDcOrderModel();
            $order_model->return_type = Bridge::RETURN_TYPE_DEFAULT;
            $order_model->product_id = $model->product_id;
            $order_model->product_name = $model->product_name;
            $order_model->product_image = $model->product_image;
            $order_model->leader_id = $model->leader_id;
            $order_model->user_id = $model->user_id;
            $order_model->pay_money = $model->pay_money;
            $order_model->return_money = $model->return_money;
            $order_model->order_sign = $model->order_sign;
            $order_model->status = Bridge::AUDIT_PASS;
            $order_model->create_time = date('Y-m-d H:i:s');
            $order_model->new_time = date('Y-m-d H:i:s', strtotime('+1 day'));
            $order_model->save();

            $income_model = new UserIncomeModel();
            $income_model->user_id = $model->leader_id;
            $income_model->type = Bridge::INCOME_TYPE_DEFAULT;
            $income_model->order_sign = $model->order_sign;
            $income_model->income = $model->return_money;
            $income_model->create_time = date('Y-m-d H:i:s');
            $income_model->save();

            Helper::pushMessage(5,['accept_user_id' => $model->leader_id,'content' => Config::ORDERMSG]);
            $transaction->commit();
            return Common::response(1, '操作成功');
        } catch(\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        }
    }

}
