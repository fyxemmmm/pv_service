<?php

namespace api\controllers;

use common\Bridge;
use common\models\UserModel;
use yii\data\ActiveDataProvider;
use common\models\UserApplyPriceModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\UserDcOrderModel;
use common\models\UserIncomeModel;
use common\models\LbImageModel;
use common\Helper;


class UserDcOrderController extends CommonController
{
    public $modelClass = '';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    /*
     * 贷超订单列表
     * */
    public function actionIndex()
    {
        $is_new = $this->get('new');  // 是否是今日订单
        $status = $this->get('status');  // 类型

        $now =  date('Y-m-d H:i:s');
        $query = UserDcOrderModel::find()->select('product_name,product_image,order_sign,user_id,status,return_money,create_time')->where(['leader_id' => $this->userId])->orderBy('id desc');

        $query = $query->andFilterWhere(['status' => $status]);

        if($is_new){
            $query = $query->andWhere(['>','new_time',$now]);
        }

        $data = Helper::usePage($query);
        foreach ($data['items'] as $k=>&$v){
            // 下款人
            $user = UserModel::find()->select('nick_name,mobile')->where(['id' => $v['user_id']])->one();
            $v['nick_name'] = $user['nick_name'] ?? '';
            $v['mobile'] = Helper::hiddenMobile($user['mobile']);
        }
        return $data;
    }


    /*
     * 收支明细 - 收入记录
     * */
    public function actionIncomeDetail(){
        $model = UserIncomeModel::find()->where(['user_id' => $this->userId])->orderBy('id desc');
        $data = Helper::usePage($model);
        $fanyong_image = LbImageModel::find()->select('image_url')->where(['type' => 0])->asArray()->scalar() ?: ''; // 返佣图
        $award_image = LbImageModel::find()->select('image_url')->where(['type' => 1])->asArray()->scalar() ?: ''; // 签到奖励图

        foreach ($data['items'] as $k=>&$v) {
            switch ($v['type']){
                case Bridge::INCOME_TYPE_DEFAULT:
                    $v['title'] = '下款返佣';
                    $v['image_url'] = $fanyong_image;
                    break;
                case Bridge::INCOME_TYPE_FIRST_AWARD:
                    $v['title'] = '新推广用户首次下款奖励金';
                    $v['image_url'] = $fanyong_image;
                    break;
                case Bridge::INCOME_TYPE_QD:
                    $v['title'] = '七日签到奖励';
                    $v['image_url'] = $award_image;
                    break;
            }
        }
        return $data;
    }


    /*
     * 收支明细 - 提现记录
     * */
    public function actionApplyList(){
        $model = UserApplyPriceModel::find()->select('id,order_sign,apply_price,status,fail_reason,create_time')->where(['user_id' => $this->userId])->orderBy('id desc');
        $data = Helper::usePage($model);
        $apply_image = LbImageModel::find()->select('image_url')->where(['type' => 2])->asArray()->scalar() ?: ''; // 签到奖励图
        foreach ($data['items'] as $k=>&$v){
            $v['title'] = '钱包提现';
            $v['image_url'] = $apply_image;
            $v['customer_name'] = Bridge::CUSTOMER_NAME;
            $v['customer_image'] = LbImageModel::find()->select('image_url')->where(['type' => 3])->scalar() ?: ''; // 客服头像
        }
        return $data;
    }


}
