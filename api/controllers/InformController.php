<?php

namespace api\controllers;

use common\Bridge;
use common\Helper;
use common\models\InformDcModel;
use common\models\LbImageModel;
use common\models\UmengPush;
use common\models\UserIncomeModel;
use common\models\UserRelationshipModel;
use common\models\UserDcOrderModel;
use common\models\UserModel;
use common\models\InformReadModel;
use common\Config;
use common\VarTmp;

class InformController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /*
     * 未读的信息
     * */
    public function actionUnreadInfo(){
        $user_register_time = $this->getUserInfo()->register_time;
        $system_read_time = InformReadModel::find()->select('read_time')->where(['user_id' => $this->userId, 'type' => Config::DCINFORM])->scalar();
        if(empty($system_read_time)){
            $has_red_dot = InformDcModel::find()->where(['>=','create_time',$user_register_time])->one(); // 是否已经阅读过 true 是
            $system_show = $has_red_dot ? true : false; // 需要展示红点?
        }else{
            $has_red_dot = InformDcModel::find()->where(['>','create_time',$system_read_time])->one();
            $system_show = $has_red_dot ? true : false;
        }

        $order_read_time = InformReadModel::find()->select('read_time')->where(['user_id' => $this->userId, 'type' => Config::ORDERINFORM])->scalar();
        if(empty($order_read_time)){
            $has_red_dot = UserDcOrderModel::find()->where(['leader_id' => $this->userId])->one();
            $order_show = $has_red_dot ? true : false;
        }else{
            $has_red_dot = UserDcOrderModel::find()->where(['>','create_time',$order_read_time])->andWhere(['leader_id' => $this->userId])->one();
            $order_show = $has_red_dot ? true : false;
        }

        $income_read_time = InformReadModel::find()->select('read_time')->where(['user_id' => $this->userId, 'type' => Config::INCOMEINFORM])->scalar();
        if(empty($income_read_time)){
            $has_red_dot = UserIncomeModel::find()->where(['user_id' => $this->userId])->one();
            $income_show = $has_red_dot ? true : false;
        }else{
            $has_red_dot = UserIncomeModel::find()->where(['>','create_time',$income_read_time])->andWhere(['user_id' => $this->userId])->one();
            $income_show = $has_red_dot ? true : false;
        }

        $team_read_time = InformReadModel::find()->select('read_time')->where(['user_id' => $this->userId, 'type' => Config::TEAMINFORM])->scalar();
        if(empty($team_read_time)){
            $has_red_dot = UserRelationshipModel::find()->where(['leader_id' => $this->userId])->one();
            $team_show = $has_red_dot ? true : false;
        }else{
            $has_red_dot = UserRelationshipModel::find()->where(['>','create_time',$team_read_time])->andWhere(['leader_id' => $this->userId])->one();
            $team_show = $has_red_dot ? true : false;
        }

        $data = [
            'system_show' => $system_show,
            'order_show' => $order_show,
            'income_show' => $income_show,
            'team_show' => $team_show
        ];

        return $data;
    }


    /*
     * 系统贷超通知列表
     * */
    public function actionSystemLists(){
        $this->read(Config::DCINFORM);
        $user_register_time = $this->getUserInfo()->register_time;
        $model = InformDcModel::find()->where(['>','create_time',$user_register_time])->orderBy('id desc');
        $data = Helper::usePage($model);
        foreach ($data['items'] as $k=>&$v){
            $v['create_time'] = date('Y-m-d', strtotime($v['create_time']));
        }
        return $data;
    }

    /*
     * 订单通知列表
     * */
    public function actionOrderLists(){
        $this->read(Config::ORDERINFORM);
        $fields = 'product_name,product_image,user_id,return_money,order_sign,create_time,status,pass_audit';
        $model = UserDcOrderModel::find()->select($fields)->where(['leader_id'=>$this->userId])->orderBy('id desc');

        $data = Helper::usePage($model);
        foreach ($data['items'] as $k=>&$v){
            $v['title'] = '您的推广用户下单啦!';
            $v['create_time'] = date('Y-m-d', strtotime($v['create_time']));
            if($v['pass_audit'] == 1) $v['status'] = "3"; // 给app展示 审核中->已下款
            unset($v['pass_audit']);
            $user_info = UserModel::find()->select('nick_name,mobile')->where(['id' => $v['user_id']])->asArray()->one();
            $mobile = preg_replace('/(.{4})(.{4})(.{3})/','$1****$3',$user_info['mobile']);
            $v['mobile'] = $mobile;
            $v['nick_name'] = empty($user_info['nick_name']) ? $mobile : $user_info['nick_name'];
        }

        return $data;
    }

    /*
     * 收入通知列表
     * */
    public function actionIncomeLists(){
        $this->read(Config::INCOMEINFORM);
        $model = UserIncomeModel::find()->where(['user_id' => $this->userId])->orderBy('id desc');
        $data = Helper::usePage($model);

        $fanyong_image = LbImageModel::find()->select('image_url')->where(['type' => 0])->asArray()->scalar() ?: ''; // 返佣图 、新用户返佣图
        $award_image = LbImageModel::find()->select('image_url')->where(['type' => 1])->asArray()->scalar() ?: ''; // 签到奖励图

        foreach ($data['items'] as $k=>&$v) {
            $v['create_time'] = date('Y-m-d', strtotime($v['create_time']));
            switch ($v['type']){
                case Bridge::INCOME_TYPE_DEFAULT: // 下款返佣
                    $v['title'] = '恭喜您获得了一笔犀金推广佣金!';
                    $v['image_url'] = $fanyong_image;
                    break;
                case Bridge::INCOME_TYPE_FIRST_AWARD:
                    $v['title'] = '恭喜您获得了犀金用户新单推广返佣!';
                    $v['image_url'] = $fanyong_image;
                    break;
                case Bridge::INCOME_TYPE_QD:
                    $v['title'] = '恭喜您获得了七天连续签到红包!';
                    $v['image_url'] = $award_image;
                    break;
            }
        }
        return $data;
    }

    public function actionTeamLists(){
        $this->read(Config::TEAMINFORM);
        $model = UserRelationshipModel::find()->where(['leader_id' => $this->userId])->orderBy('id desc');
        $data = Helper::usePage($model);
        $count = count($data['items']);
        $count = (VarTmp::$page -1 ) * VarTmp::$per_page + $count;
        foreach ($data['items'] as $k=>&$v) {
            $v['create_time'] = date('Y-m-d', strtotime($v['create_time']));
            $v['member_num'] = (string)$count --;
            $user_info = UserModel::find()->select('nick_name,mobile')->where(['id' => $v['user_id']])->asArray()->one();
            $mobile = preg_replace('/(.{4})(.{4})(.{3})/','$1****$3',$user_info['mobile']);
            $v['nick_name'] = empty($user_info['nick_name']) ? $mobile : $user_info['nick_name'];
            unset($v['leader_id']);
        }

        return $data;
    }

    /*
     * 列表已读
     * */
    public function read($type){
        $model = InformReadModel::find()->where(['user_id' => $this->userId, 'type' => $type])->one();
        if(!$model){
            $model = new InformReadModel();
            $model->user_id = $this->userId;
            $model->type = $type;
            $model->read_time = date('Y-m-d H:i:s');
            $model->save();
            return;
        }
        $model->read_time = date('Y-m-d H:i:s');
        $model->save();
        return;
    }


    public function actionTest()
    {
        $umeng = new UmengPush();
//        $umeng->sendAndroidUnicast('AnjHBlWBVHCVZmU_FQes3bCUlmcstUf73hCfnUOjdv75','aaaaaa');
//        Helper::pushMessage(5,['accept_user_id' => 253,'content' => '您有一条新的订单通知']);
//        $umeng->sendAndroidBroadcast('标题','描述很长描述很长描述很长描述很长描述很长描述很长描述很长描述很长描述很长描述很长描述很长描述很长描述很长',['type' => 7,'title' => '标题', 'describe'=> '描述信息','create_time' => date('Y-m-d H:i:s')]);

        exit;

//        $umeng->sendAndroidBroadcast('测试');
    }


}
