<?php

namespace api\controllers;

use common\Bridge;
use common\Helper;
use common\models\BannerModel;
use common\models\Common;
use common\models\CouponBannerModel;
use common\models\CouponModel;
use common\models\CouponUserModel;
use common\models\GoodsTypeModel;
use common\models\JushengVipModel;
use common\models\User;
use common\VarTmp;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\GoodsIndex;
use api\models\GoodsView;
use common\models\CheckOpenLogic;
use common\models\GoodsUserShareModel;
use Yii;

class CouponController extends CommonController
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
            'except' => ['index', 'view', 'share', 'get-type', 'get-type-index', 'add-coupon']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    /*
     * 优惠券列表
     * */
    public function actionIndex()
    {
        /*
        $query = BannerModel::find()
            ->select('url')
            ->where(['type'=>3],['type'=>4])
            ->orderBy('create_time desc');
        */
        /*
        $coupon_list = CouponModel::find()
            ->select('coupon_code,overdue,name,description,amount,limit_amount')
            ->where(['status'=>1])
            ->orderBy('create_time desc')
            ->asArray()
            ->all();
        */
        $alert = CouponBannerModel::find()->where(['type'=>1])->one();
        $left = CouponBannerModel::find()->where(['type'=>2])->one();
        //标题  web_url  分享标题 分享内容 分享链接
        $res = [
            'alert' => $alert['img_url'],
            'left' => $left['img_url'],
            'web_url' => $alert['web_url'],
            'share_url' => $alert['share_url'],
            'title' => $alert['title'],
            'status' => $alert['status'],
            'share_title' => $alert['share_title'],
            'share_desc' => $alert['share_desc']
        ];
        Helper::formatData($res,2);
        return $res;
    }

    /*
     * 购买会员优惠券列表
     * */
    public function  actionCoupon(){
        $coupon_list = CouponModel::find()
            ->select('coupon_code,overdue,name,description,amount,limit_amount')
            ->where(['status'=>1])
            ->orderBy('create_time desc')
            ->asArray()
            ->all();
        return $coupon_list;
    }

    /*
     * 优惠券详情
     * */
    public function actionView($id)
    {
        $query = CouponModel::find()->select('coupon_code,overdue,name,description,amount,limit_amount')->where(['id' => $id, 'status' => 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    /*
     * 优惠券领取
     * */
    public function actionAddCoupon()
    {
        $coupon_code = $this->post('coupon_code') ?? '';
        $coupon = CouponModel::find()
            ->select('coupon_code,overdue,name,description,amount,limit_amount')
            ->where(['coupon_code' => $coupon_code, 'status' => 1])->one();
        if ($coupon) {
            $coupon_status = $this->post('status') ?? 0;
            $start_time = date("Y-m-d 00:00:00");
            $end_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00:00')) + 86400 * $coupon['overdue'] - 1);
            $userinfo = $this->getUserInfo();
            $user_id = $userinfo['id'];
            $is_vip = JushengVipModel::find()
                ->where(['vip_type' => 2,'user_id' => $user_id])->one();
            if($is_vip){
                $coupon_user = CouponUserModel::find()
                    ->where(['coupon_code' => $coupon_code, 'user_id' => $user_id])->one();
                if ($coupon_user) {
                    return Common::response(0, '您已领取,请勿重复领取！');
                } else {
                    $model = new CouponUserModel();
                    $model->user_id = $user_id;
                    $model->coupon_code = $coupon_code;
                    $model->coupon_status = $coupon_status;
                    $model->start_time = $start_time;
                    $model->end_time = $end_time;
                    if ($model->save(false)) return Common::response(1, 'success');
                    return Common::response(0, 'failure', $model->getErrors());
                }
            }else{
                return Common::response(0, '请开通永久VIP后领取！');
            }
        } else {
            return Common::response(0, 'failure', '参数错误');
        }
    }

    /*
     * 用户优惠券列表
     * */
    public function actionUserCoupon()
    {
        $price_total = $this->get('price_total');
        if(isset($price_total) && !empty($price_total)){
            $userinfo = $this->getUserInfo();
            $user_id = $userinfo['id'];
            $time = date('Y-m-d H:i:s');
            //可用券
            $coupon_list = CouponUserModel::find()
                ->alias('a')
                ->select('a.coupon_code,a.coupon_status,a.start_time,a.end_time,b.name,b.description,b.amount,b.limit_amount')
                ->leftJoin('coupon b', 'a.coupon_code = b.coupon_code')
                ->where(['a.user_id' => $user_id])
                ->andwhere(['a.coupon_status' => 0])
                ->andwhere(['<', 'a.start_time', $time])
                ->andWhere(['>', 'a.end_time', $time])
                ->andWhere(['<=', 'b.limit_amount', (float)$price_total])
                ->orderBy('a.end_time desc')
                ->asArray()
                ->all();
            foreach ($coupon_list as $k=>&$v){
                $coupon_list[$k]['status'] = $this->getExpireInfo($v['end_time']);
                $coupon_list[$k]['start_time'] = date('Y-m-d', strtotime($v['start_time']));
                $coupon_list[$k]['end_time'] = date('Y-m-d', strtotime($v['end_time']));
            }
            //暂不可用券
            $disable_coupon = CouponUserModel::find()
                ->alias('a')
                ->select('a.coupon_code,a.coupon_status,a.start_time,a.end_time,b.name,b.description,b.amount,b.limit_amount')
                ->leftJoin('coupon b', 'a.coupon_code = b.coupon_code')
                ->where(['a.user_id' => $user_id])
                ->andwhere(['a.coupon_status' => 0])
                ->andwhere(['or',['>','a.start_time',$time],['and',['<','a.start_time',$time],['>','a.end_time',$time],['>','b.limit_amount',(float)$price_total]]])
                ->orderBy('a.end_time desc')
                ->asArray()
                ->all();
            foreach ($disable_coupon as $k=>&$v){
                $disable_coupon[$k]['start_time'] = date('Y-m-d', strtotime($v['start_time']));
                $disable_coupon[$k]['end_time'] = date('Y-m-d', strtotime($v['end_time']));
            }
            $res = [
                'coupon_list' =>$coupon_list,
                'disable_coupon' =>$disable_coupon
            ];
            Helper::formatData($res,2);
            return $res;
        }else{
            $userinfo = $this->getUserInfo();
            $user_id = $userinfo['id'];
            //可用券
            $coupon_list = CouponUserModel::find()
                ->alias('a')
                ->select('a.coupon_code,a.coupon_status,a.start_time,a.end_time,b.name,b.description,b.amount,b.limit_amount')
                ->leftJoin('coupon b', 'a.coupon_code = b.coupon_code')
                ->where(['a.user_id' => $user_id])
                ->andwhere(['a.coupon_status' => 0])
                ->orderBy('a.end_time desc')
                ->asArray()
                ->all();
            foreach ($coupon_list as $k=>&$v){
                $coupon_list[$k]['status'] = $this->getExpireInfo($v['end_time']);
                $coupon_list[$k]['start_time'] = date('Y-m-d', strtotime($v['start_time']));
                $coupon_list[$k]['end_time'] = date('Y-m-d', strtotime($v['end_time']));
            }
            Helper::formatData($coupon_list,2);
            return $coupon_list;
        }

    }

    /*
     * 失效优惠券列表
     * */
    public function actionLoseCoupon()
    {
        $userinfo = $this->getUserInfo();
        $user_id = $userinfo['id'];
        $time = date('Y-m-d H:i:s');
        //失效券 1已使用 2已过期
        $lose_coupon = CouponUserModel::find()
            ->alias('a')
            ->select('a.coupon_code,a.coupon_status,a.end_time,b.name,b.description,b.amount,b.limit_amount')
            ->leftJoin('coupon b', 'a.coupon_code = b.coupon_code')
            ->where(['a.user_id' => $user_id])
            ->andwhere(['or',['a.coupon_status' => 1],['a.coupon_status' => 2]])
            ->andwhere(['<','a.end_time',$time])
            ->orderBy('a.end_time desc')
            ->asArray()
            ->all();
        if(!empty($lose_coupon)){
            foreach ($lose_coupon as $k=>&$v){
                $lose_coupon[$k]['end_time'] = date('Y-m-d', strtotime($v['end_time']));
            }
        }
        Helper::formatData($lose_coupon,2);
        return $lose_coupon;
    }

    public function  actionUserCouponc()
    {
        //Common::quxiao('325');
        /*
        $jusheng_vip = JushengVipModel::find()->where(['user_id'=>1,'vip_type'=>2])->one();
        if($jusheng_vip){
            $is_vip = 0;
        }else{
            $is_vip = 1;
        }
        var_dump($is_vip);
        */

        $goods_coupon = CouponUserModel::find()
            ->select('coupon_user.*,coupon.name,coupon.description,coupon.amount,coupon.limit_amount')
            ->leftJoin('coupon','coupon.coupon_code = coupon_user.coupon_code')
            ->where(['coupon_user.user_id' => 1])
            ->andwhere(['coupon_user.coupon_status' => 0])
            ->asArray()
            ->all();
        foreach ($goods_coupon as $k=>&$v){
            $v['type'] = '4';
            $v['status'] = $this->getExpireInfo($v['end_time']);
            $v['start_time'] = date('Y-m-d', strtotime($v['start_time']));
            $v['end_time'] = date('Y-m-d', strtotime($v['end_time']));
        }
        return $goods_coupon;

    }

    /*
     * 优惠券分类
     * */
    public function actionGetType()
    {
        $data = GoodsTypeModel::find()->orderBy('order asc')->asArray()->all();
        return $data;
    }

    private function getExpireInfo($end_time){
        $end_time = date('Y-m-d',strtotime($end_time));
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime("+1 day"));
        $two_days_later = date('Y-m-d', strtotime("+2 day"));
        if($end_time == $today){
            $status = 1; // 今天过期
        }elseif ($end_time == $tomorrow){
            $status = 2; // 明天过期
        }else if ($end_time == $two_days_later){
            $status = 3; // 后天过期
        }else{
            $status = 0;
        }
        return (string)$status;
    }


}
