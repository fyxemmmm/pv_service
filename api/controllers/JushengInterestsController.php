<?php

namespace api\controllers;

use common\AliPay;
use common\Bridge;
use common\models\Jusheng;
use common\models\JushengCouponModel;
use common\models\JushengSixImgModel;
use common\models\JushengVipModel;
use common\models\CouponUserModel;
use common\models\JushengVipOrderModel;
use common\WxPay;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\CheckOpenLogic;
use common\models\Common;
use common\models\XiaoyingVipOrderModel;
use Yii;

class JushengInterestsController extends CommonController
{
    public $modelClass = '';
    public $jusheng;
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->jusheng = new Jusheng();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['goods99-info-list','allcategorylist']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    // 劵列表
    public function actionGetCoupon()
    {
        $type = $this->get('type');  // 1 未过期的
        $user_id = $this->userId;
        $video_arr = [];
        $tel_arr = []; // 话费

        $video_coupon_img = 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/video.png';
        $video_disabled_img = 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/tel_exp.png';
        $tel_coupon_img = 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/tel.png';
        $tel_disabled_img = 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/video_exp.png';

        if($type == 1){
            $data = JushengCouponModel::find()->select('id,start_time,end_time,type,supplier_coupon_code as agentProductCode,coupon_code as agentCouponId')
                                              ->where(['user_id' => $user_id, 'coupon_status' => 0])
                                              ->andWhere(['>=','end_time',date('Y-m-d H:i:s')])
                                              ->asArray()
                                              ->all();
            foreach ($data as $k=>&$v){
                $start_time_stamp = strtotime($v['start_time']);
                $end_time_stamp = strtotime($v['end_time']);
                // 券当前时刻是否可用
                $now = time();
                if($start_time_stamp <= $now && $end_time_stamp >= $now){
                    $v['can_use'] = true;
                }else{
                    $v['can_use'] = false;
                }

                if($v['type'] == 1){  // 视频劵
                    $v['title'] = '10元视频权益周卡';
                    $v['desc'] = '腾讯、爱奇艺、优酷三选一';
                    $v['img_url'] = $video_coupon_img;
                    $v['status'] = $this->getExpireInfo($v['end_time']);
                    $v['start_time'] = date('Y-m-d', $start_time_stamp);
                    $v['end_time'] = date('Y-m-d', $end_time_stamp);
                    $video_arr[] = $v;
                }elseif ($v['type'] == 2 || $v['type'] == 3){
                    if($v['type'] == 2){
                        $v['title'] = '10元话费代金券';
                        $v['desc'] = '移动、联通、电信三网通';
                    }else{
                        $v['title'] = '5元话费代金券';
                        $v['desc'] = '移动、联通、电信三网通';
                    }
                    $v['img_url'] = $tel_coupon_img;
                    $v['status'] = $this->getExpireInfo($v['end_time']);
                    $v['start_time'] = date('Y-m-d', $start_time_stamp);
                    $v['end_time'] = date('Y-m-d', $end_time_stamp);
                    $tel_arr[] = $v;
                }
            }
            $goods_coupon = CouponUserModel::find()
                ->select('coupon_user.*,coupon.name,coupon.description,coupon.amount,coupon.limit_amount')
                ->leftJoin('coupon','coupon.coupon_code = coupon_user.coupon_code')
                ->where(['coupon_user.user_id' => $user_id])
                ->andwhere(['coupon_user.coupon_status' => 0])
                ->asArray()
                ->all();
            foreach ($goods_coupon as $k=>&$v){
                $v['type'] = '4';
                $v['status'] = $this->getExpireInfo($v['end_time']);
                $v['start_time'] = date('Y-m-d', strtotime($v['start_time']));
                $v['end_time'] = date('Y-m-d', strtotime($v['end_time']));
            }
            return ['video' => $video_arr, 'tel' => $tel_arr, 'goods' => $goods_coupon];
        }elseif ($type == 2){  // 已经过期或者使用的
            $data = JushengCouponModel::find()->select('id,start_time,end_time,type,coupon_status')
                                              ->where(['user_id' => $user_id, 'coupon_status' => [1,2,3]])
                                              ->orWhere(['and',['coupon_status' => 0],['<','end_time',date('Y-m-d H:i:s')]])
                                              ->asArray()
                                              ->all();

            foreach ($data as $k=>&$v){
                $v['start_time'] = '';
                if($v['coupon_status'] == 3) $v['coupon_status'] = "1";  // 使用中,我们这里没有这个状态 所以直接变成了已经使用了
                if($v['coupon_status'] === "0") $v['coupon_status'] = "2"; // 超时没使用的,已过期
                if($v['type'] == 1){  // 视频劵
                    $v['title'] = '10元视频权益周卡';
                    $v['desc'] = '腾讯、爱奇艺、优酷三选一';
                    $v['img_url'] = $video_disabled_img;
                    $v['end_time'] = date('Y-m-d', strtotime($v['end_time']));
                    $video_arr[] = $v;
                }elseif ($v['type'] == 2 || $v['type'] == 3){
                    if($v['type'] == 2){
                        $v['title'] = '10元话费代金券';
                        $v['desc'] = '移动、联通、电信三网通';
                    }else{
                        $v['title'] = '5元话费代金券';
                        $v['desc'] = '移动、联通、电信三网通';
                    }
                    $v['img_url'] = $tel_disabled_img;
                    $v['end_time'] = date('Y-m-d', strtotime($v['end_time']));
                    $tel_arr[] = $v;
                }
            }
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
            return ['video' => $video_arr, 'tel' => $tel_arr, 'goods' => $lose_coupon];
        }
        return Common::response(0, 'type类型错误');
    }


    public function actionSection1(){
        $gift_bag_num = JushengCouponModel::find()->where(['user_id' => $this->userId, 'coupon_status' => 0])->count(); // 我的礼包
        $coupon = CouponUserModel::find()
            ->where(['user_id' => $this->userId])
            ->andwhere(['coupon_status' => 0])
            ->count();
        $gift_bag_num +=  $coupon;
        $vault = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/my/vault",['userId' => (string)$this->userId]);
        $is_month_vip = JushengVipModel::find()->where(['user_id' => $this->userId,'vip_type' => 3])->andWhere(['>','expire_time',date('Y-m-d H:i:s')])->one(); // 月卡会员
        if($is_month_vip){
            $expire_time = date('Y-m-d', strtotime($is_month_vip->expire_time));
            $res = [
                'type' => '3', // 月卡会员
                'expire_time' => $expire_time,
                'info1' => "",
                'info2' => '',
                'price_now' => '',
                'price_origin' => '',
                'gift_bag_num' => $gift_bag_num,
                'cash_amount' => $vault['totalAmt'], // 订单金额
                'money_saved' => $vault['totalAmt']
            ];
        }else{
            $is_season_vip = JushengVipModel::find()->where(['user_id' => $this->userId,'vip_type' => 1])->andWhere(['>','expire_time',date('Y-m-d H:i:s')])->one(); // 季卡会员
            if($is_season_vip){
                $expire_time = date('Y-m-d', strtotime($is_season_vip->expire_time));
                $res = [
                    'type' => '1', // 季度会员
                    'expire_time' => $expire_time,
                    'info1' => "",
                    'info2' => '',
                    'price_now' => '',
                    'price_origin' => '',
                    'gift_bag_num' => $gift_bag_num,
                    'cash_amount' => $vault['totalAmt'], // 订单金额
                    'money_saved' => $vault['totalAmt']
                ];
            }else{
                $lifetime = JushengVipModel::find()->where(['user_id' => $this->userId,'vip_type' => 2])->one(); // 是否是终生会员
                if($lifetime){
                    $type = '2';
                    $money_saved = (string)($vault['totalAmt'] + 110);
                }else{
                    $type = '0';
                    $money_saved = $vault['totalAmt'];
                }

                $res = [
                    'type' => $type, // 终生会员
                    'expire_time' => '',
                    'info1' => "终生享九大权益",
                    'info2' => '限时4折优惠',
                    'price_now' => '199.9',
                    'price_origin' => '499',
                    'gift_bag_num' => $gift_bag_num,
                    'cash_amount' => $vault['totalAmt'], // 订单金额
                    'money_saved' => $money_saved
                ];
            }
        }

        $bridge = new Bridge();
        $invite_sign = Bridge::genSign($this->userId);
        $vip_share_url = $bridge->vip_wap_url . '/h5_items/huiyuanshare.html?sign=' . $invite_sign;
        //是否是新开会员
        $conut = (int)JushengVipOrderModel::find()
            ->where(['user_id' => $this->userId])
            ->andWhere('update_time > 0')
            ->count();
        if($conut > 0){
            $res['is_new_vip'] = 0;
        }else{
            $res['is_new_vip'] = 1;
        }
        $res['vip_share_url'] = $vip_share_url;
        return $res;
    }


    public function actionSection2(){
        $user_id = $this->userId;
        $banner = [
            [
                'type' => "1", // 饿了么会员 跳他们页面
                'image_url' => 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/ele.png',
                'url' => '' // 暂未给出
            ],
            [
                'type' => "2", // 商城
                'image_url' => 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/shop.png',
                'url' => ''
            ]
        ];

        $CheckOpenLogic = new CheckOpenLogic();
        $open = $CheckOpenLogic->check();
        $headers = Yii::$app->request->headers;
        $os = $headers->get('name', '');
        $open_channel = $headers->get('openChannel', '');

        if ($open) {
            if ('xijing' == $os) {
                if ($open_channel) {
                    $banner[] = [
                        'type' => "3", // 推广
                        'image_url' => 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/promote.png',
                        'url' => ''
                    ];
                }
            } else {
                $banner[] = [
                    'type' => "3", // 推广
                    'image_url' => 'https://xijin.oss-cn-shanghai.aliyuncs.com/quanyi/promote.png',
                    'url' => ''
                ];
            }
        }

        $goods99 = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/tbk/goods99InfoList",['userId' => (string)$user_id]); // 9.9包邮
        if(empty($goods99)){
            $lst99 = [];
        }else{
            $count = 0;
            $lst99 = [];
            $goods99_lst =  $goods99['m9ProductList'];
            foreach ($goods99_lst as $k=>$v){
                foreach ($v as $kk => $vv){
                    if($count == 10) break 2;
                    $lst99[] = $vv;
                    $count ++;
                }
            }
        }
        $six_cate = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/tbk/allcategorylist",['userId' => (string)$user_id]); //  6大类目

        $six_cate_imgs = array_column(JushengSixImgModel::find()->asArray()->all(),'image_url');
        $i = 0;
        foreach ($six_cate as $k=>&$v){
            $v['imgBgUrlVip'] = $six_cate_imgs[$i];
            $i ++;
        }

        $res = [
            'banner' => $banner,
            'm9ProductList' => $lst99,
            'six_cate' => $six_cate
        ];
        array_splice($res['six_cate'],4,1);
        return $res;
    }

    // 查询所有类目下的品牌  第三部分区域
    public function actionSection3(){
        $headers = Yii::$app->request->headers;
        $platform = $headers->get('platform');
        if ($platform && 'kuaishou' == $platform) return [];

        $user_id = $this->userId;
        $data = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/queryAllCategory",['userId' => (string)$user_id]);
        return $data['categoryList'];
    }

    // 我的金库
    public function actionVault(){
        $user_id = $this->userId;
        $data = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/my/vault",['userId' => (string)$user_id]);
        return $data;
    }


    // 获得品牌的分类的名称 （一级）
    public function actionGetBrandCate(){
        $user_id = $this->userId;
        $data = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/queryAllCategory",['userId' => (string)$user_id]);
        $list = [];
        foreach ($data['categoryList'] as $k=>$v){
            $arr = [];
            $arr['id'] = $v['id'];
            $arr['name'] = $v['name'];
            $list[] = $arr;
        }
        return $list;
    }

    // 获得品牌下的商品列表（一级下面的）
    public function actionBrandProductList(){
        $user_id = $this->userId;
        $cate_id = $this->get('cate_id');
        $data = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/queryAllCategory",['userId' => (string)$user_id]);

        $need_to_query = [];
        foreach ($data['categoryList'] as $k=>$v){
//            var_dump($v);exit();
//            if($v['id'] == $cate_id){
//                var_dump($v['brandInfoList']);exit();
                $need_to_query_ids = array_column($v['brandInfoList'],'id');
                var_dump($need_to_query_ids);exit();
        }

        var_dump($data);exit();
        $data = $this->jusheng->getData("/appweb/data/ws/rest/vip/agent/brand/products",['brandId' => (string)$brand_id]);
//        var_dump($data);exit();
        return $data;
    }

    //月卡19.9元 VIP订单页面 type == 3
    // 创建支付订单
    public function actionCreateOrder(){
        $vip_type = $this->post('vip_type'); // 1 季度会员 2 终生会员 3 月卡会员 10 贷超解锁会员
        $pay_type = $this->post('pay_type'); // 1 支付宝 2 微信(暂留)
        $pay_version = $this->post('pay_version') ?? '';

        $vip_model = JushengVipModel::find()->where(['user_id' => $this->userId])->one();
        if($vip_model && 10 != $vip_type){
            if($vip_model->vip_type == 2)  return Common::response(0, '您已是终身会员');
        }

        if($vip_type != 1 && $vip_type != 2 && $vip_type != 3 && $vip_type != 10) return Common::response(0, '会员类型错误');
        if($pay_type != 1 && $pay_type != 2) return Common::response(0, '支付类型错误');
        $order_sign = Common::genOrderSign();
        if($pay_type == 1){
            $ali = new AliPay($pay_version);
        }elseif($pay_type == 2){
            $wx = new WxPay($pay_version);
        }
        if($vip_type == 1){
            $price = 69.9;
            $order = [
                'subject' => '犀京季卡会员',
                'order_sign' => $order_sign,
                'price_total' => $price
            ];
            if($pay_type == 1){
                $order_str = $ali->createOrder($order, 'interests_quarter');
            }elseif($pay_type == 2){
                $order_str = $wx->wechat_pay( $order['subject'], $order_sign,$price,'APP');
            }

        }elseif($vip_type == 2){
            $price = 199.9;
            $order = [
                'subject' => '犀京终生会员',
                'order_sign' => $order_sign,
                'price_total' => $price
            ];
            if($pay_type == 1){
                $order_str = $ali->createOrder($order, 'interests_forever');
            }elseif($pay_type == 2){
                $order_str = $wx->wechat_pay( $order['subject'], $order_sign,$price,'APP');
            }
        } elseif($vip_type == 3){
            $conut = (int)JushengVipOrderModel::find()
                ->where(['user_id' => $this->userId])
                ->andWhere('update_time > 0')
                ->count();
            if($conut > 0){
                $price = 19.9;
            }else{
                $price = 19.9;
            }
            $order = [
                'subject' => '犀京月卡会员',
                'order_sign' => $order_sign,
                'price_total' => $price
            ];
            if($pay_type == 1){
                $order_str = $ali->createOrder($order, 'interests_month');
            }elseif($pay_type == 2){
                $order_str = $wx->wechat_pay( $order['subject'], $order_sign,$price,'APP');
            }
        } elseif($vip_type == 10){
            $price = 19.9;
            $order = [
                'subject' => '啸鹰会员',
                'order_sign' => $order_sign,
                'price_total' => $price
            ];
            if($pay_type == 1){
                $order_str = $ali->createOrder($order, 'interests_xiaoying');
            }elseif($pay_type == 2){
                $order_str = $wx->wechat_pay( $order['subject'], $order_sign,$price,'APP');
            }

            $attributes = [
                'user_id' => $this->userId,
                'order_sign' => $order_sign,
                'price' => $price,
                'pay_type' => $pay_type,
                'trade_status' => 0,//未支付订单
                'create_time' => date('Y-m-d H:i:s')
            ];
            $model = new XiaoyingVipOrderModel();
            $model->setAttributes($attributes);

            if($model->save())  return Common::response(1, '订单创建成功', $order_str);
        }
        $attributes = [
            'user_id' => $this->userId,
            'order_sign' => $order_sign,
            'price' => $price,
            'vip_type' => $vip_type,
            'pay_type' => $pay_type,
            'trade_status' => 0,//未支付订单
            'create_time' => date('Y-m-d H:i:s')
        ];

        $model = new JushengVipOrderModel();
        $model->setAttributes($attributes);
        if($model->save())  return Common::response(1, '订单创建成功', $order_str);
        return Common::response(0, '订单创建失败', $model->getErrors());
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

