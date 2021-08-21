<?php

namespace service\controllers;

use common\models\ChannelModel;
use common\models\GoodsSpecificationModel;
use common\models\GoodsTypeModel;
use common\models\UserDcOrderModel;
use common\models\UserloanModel;
use yii\data\ActiveDataProvider;
use common\models\Common;
use Yii;
use common\models\GoodsModel;
use common\models\AdminModel;
use common\models\GoodsClickModel;
use common\models\GoodsOrderModel;
use common\models\GoodsOrderSubModel;
use common\models\UserModel;
use common\models\JushengVipOrderModel;
use common\models\XiaoyingVipOrderModel;
use service\models\Admin;
use service\models\Goods;

class BuyDataController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    /**
     * 用户每日消费数据
     */
    public function actionBuylist()
    {
        $time = $this->get('start_time') ?? date("Y-m-d");
        $type = $this->get('type') ?? 1;
        //$time = date("2020-06-24");
        $time2 = date("Y-m-d", strtotime("+1 day", strtotime($time)));
        $perPage = 20;
        $page = $this->get('page', 1);
        /*
        //犀京会员今日付费
        $xijing_num = JushengVipOrderModel::find()
            ->alias('a')
            ->select('b.platform,count(a.user_id) as xijing_num')
            ->leftJoin('user b','b.id = a.user_id')
            ->where(['>', 'a.update_time', $time])
            ->andWhere(['<', 'a.update_time', $time2])
            ->andWhere(['=', 'a.trade_status', 1])
            ->groupBy('b.platform')
            ->asArray()
            ->all();
        //啸鹰会员今日付费
        $xiaoying_num = XiaoyingVipOrderModel::find()
            ->alias('a')
            ->select('b.platform,count(a.user_id) as xiaoying_num')
            ->leftJoin('user b','b.id = a.user_id')
            ->where(['>', 'a.update_time', $time])
            ->andWhere(['<', 'a.update_time', $time2])
            ->andWhere(['=', 'a.trade_status', 1])
            ->groupBy('b.platform')
            ->asArray()
            ->all();
        */
        $rows = [];
        if ((int)$type == 1) {
            //犀京会员付费（今日注册并消费）
            $data = JushengVipOrderModel::find()
                //->select('b.platform,count(a.user_id) as xijing_num')
                //->select('price,update_time,vip_type')
                ->where(['>', 'update_time', $time])
                ->andWhere(['<', 'update_time', $time2])
                ->andWhere(['=', 'trade_status', 1])
                ->orderBy('update_time desc');
                //->groupBy('b.platform')
                //->asArray()
                //->all();
            $dataProvider = new ActiveDataProvider([
                'query' => $data,
            ]);
            $models = $dataProvider->getModels();
            foreach ($models as $key => $val){
                $rows[$key] = $val->attributes;
                $user = UserModel::find()->select('mobile,platform')->where(['id'=>(int)$val->user_id])->andwhere(['>', 'register_time', $time])->andwhere(['<', 'register_time', $time2])->one();
                if($user){
                    $rows[$key]['mobile'] = $user['mobile'];
                    $rows[$key]['platform'] = $user['platform'];
                }else{
                    unset($rows[$key]);
                }
            }
            foreach ($rows as $key => $val) {
                if((int)$val['vip_type'] == 1){
                    $rows[$key]['vip_type'] = '季卡会员';
                }elseif ((int)$val['vip_type'] == 2){
                    $rows[$key]['vip_type'] = '永久会员';
                }elseif ((int)$val['vip_type'] == 3){
                    $rows[$key]['vip_type'] = '月卡会员';
                }
                $channel = UserloanModel::find()->select('channel_id')->where(['mobile' => $val['mobile']])->scalar();
                if (!empty($channel)) {
                    $channel_name = ChannelModel::find()->select('name')->where(['id' => $channel])->scalar();
                    $rows[$key]['channel_name'] = $channel_name;
                    $rows[$key]['product_name'] = '犀京会员';
                } else {
                    $rows[$key]['channel_name'] = '';
                    $rows[$key]['product_name'] = '犀京会员';
                }
            }
        } elseif ((int)$type == 2) {
            //啸鹰会员付费（今日注册并消费）
            $data = XiaoyingVipOrderModel::find()
                //->select('b.platform,count(a.user_id) as xiaoying_num')
                //->select('price,update_time')
                ->where(['>', 'update_time', $time])
                ->andWhere(['<', 'update_time', $time2])
                ->andWhere(['=', 'trade_status', 1])
                ->orderBy('update_time desc');
                //->groupBy('b.platform')
                //->asArray()
                //->all();
            $dataProvider = new ActiveDataProvider([
                'query' => $data,
            ]);
            $models = $dataProvider->getModels();
            foreach ($models as $key => $val){
                $rows[$key] = $val->attributes;
                $user = UserModel::find()->select('mobile,platform')->where(['id'=>(int)$val->user_id])->andwhere(['>', 'register_time', $time])->andwhere(['<', 'register_time', $time2])->one();
                if($user){
                    $rows[$key]['mobile'] = $user['mobile'];
                    $rows[$key]['platform'] = $user['platform'];
                }else{
                    unset($rows[$key]);
                }
            }
            foreach ($rows as $key => $val) {
                $channel = UserloanModel::find()->select('channel_id')->where(['mobile' => $val['mobile']])->scalar();
                if (!empty($channel)) {
                    $channel_name = ChannelModel::find()->select('name')->where(['id' => $channel])->scalar();
                    $rows[$key]['channel_name'] = $channel_name;
                    $rows[$key]['product_name'] = '啸鹰会员';
                    $rows[$key]['vip_type'] = '永久会员';
                } else {
                    $rows[$key]['channel_name'] = '';
                    $rows[$key]['product_name'] = '啸鹰会员';
                    $rows[$key]['vip_type'] = '永久会员';
                }
            }
        } elseif ((int)$type == 3) {
            //犀京会员付费（之前注册）
            $data = JushengVipOrderModel::find()
                //->select('b.platform,count(a.user_id) as buy_today')
                //->select('price,update_time,vip_type')
                ->where(['>', 'update_time', $time])
                ->andWhere(['<', 'update_time', $time2])
                ->andWhere(['=', 'trade_status', 1])
                ->orderBy('update_time desc');
                //->groupBy('b.platform')
                //->asArray()
                //->all();
            $dataProvider = new ActiveDataProvider([
                'query' => $data,
            ]);
            $models = $dataProvider->getModels();
            foreach ($models as $key => $val){
                $rows[$key] = $val->attributes;
                $user = UserModel::find()->select('mobile,platform')->where(['id'=>(int)$val->user_id])->andwhere(['<', 'register_time', $time])->one();
                if($user){
                    $rows[$key]['mobile'] = $user['mobile'];
                    $rows[$key]['platform'] = $user['platform'];
                }else{
                    unset($rows[$key]);
                }
            }
            foreach ($rows as $key => $val) {
                if((int)$val['vip_type'] == 1){
                    $rows[$key]['vip_type'] = '季卡会员';
                }elseif ((int)$val['vip_type'] == 2){
                    $rows[$key]['vip_type'] = '永久会员';
                }elseif ((int)$val['vip_type'] == 3){
                    $rows[$key]['vip_type'] = '月卡会员';
                }
                $channel = UserloanModel::find()->select('channel_id')->where(['mobile' => $val['mobile']])->scalar();
                if (!empty($channel)) {
                    $channel_name = ChannelModel::find()->select('name')->where(['id' => $channel])->scalar();
                    $rows[$key]['channel_name'] = $channel_name;
                    $rows[$key]['product_name'] = '犀京会员';
                } else {
                    $rows[$key]['channel_name'] = '';
                    $rows[$key]['product_name'] = '犀京会员';
                }
            }
        } elseif ((int)$type == 4) {
            //啸鹰会员付费（之前注册）
            $data = XiaoyingVipOrderModel::find()
                //->select('b.platform,count(a.user_id) as buy_today')
                //->select('price,update_time')
                ->where(['>', 'update_time', $time])
                ->andWhere(['<', 'update_time', $time2])
                ->andWhere(['=', 'trade_status', 1])
                ->orderBy('update_time desc');
                //->groupBy('b.platform')
                //->asArray()
                //->all();
            $dataProvider = new ActiveDataProvider([
                'query' => $data,
            ]);
            $models = $dataProvider->getModels();
            foreach ($models as $key => $val){
                $rows[$key] = $val->attributes;
                $user = UserModel::find()->select('mobile,platform')->where(['id'=>(int)$val->user_id])->andwhere(['<', 'register_time', $time])->one();
                if($user){
                    $rows[$key]['mobile'] = $user['mobile'];
                    $rows[$key]['platform'] = $user['platform'];
                }else{
                    unset($rows[$key]);
                }
            }
            foreach ($rows as $key => $val) {
                $channel = UserloanModel::find()->select('channel_id')->where(['mobile' => $val['mobile']])->scalar();
                if (!empty($channel)) {
                    $channel_name = ChannelModel::find()->select('name')->where(['id' => $channel])->scalar();
                    $rows[$key]['channel_name'] = $channel_name;
                    $rows[$key]['product_name'] = '啸鹰会员';
                    $rows[$key]['vip_type'] = '永久会员';
                } else {
                    $rows[$key]['channel_name'] = '';
                    $rows[$key]['product_name'] = '啸鹰会员';
                    $rows[$key]['vip_type'] = '永久会员';
                }

            }
        }
        foreach ($rows as $key => $val) {
            unset($rows[$key]['id']);
            unset($rows[$key]['order_sign']);
            unset($rows[$key]['pay_type']);
            unset($rows[$key]['trade_status']);
            unset($rows[$key]['create_time']);
        }
        $count = count($rows);

        return [
            'items' => $rows,
            '_meta' => [
                'totalCount' => $count,
                'pageCount' => ceil($count / $perPage),
                'currentPage' => $page
            ]
        ];
    }


    /**
     * 管理员列表
     *
     */
    public function actionAdminList()
    {
        //上货人列表
        $rows = AdminModel::find()
            ->select('id,name,realname')
            ->asArray()
            ->all();
        $count = count($rows);
        $perPage = 50;

        return [
            'items' => $rows,
            '_meta' => [
                'totalCount' => $count,
                'pageCount' => ceil($count / $perPage)
            ]
        ];
    }

}
