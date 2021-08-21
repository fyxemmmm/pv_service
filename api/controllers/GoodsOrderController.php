<?php

namespace api\controllers;

use api\models\GoodsOrderViewV2;
use common\AliPay;
use common\models\CouponUserModel;
use common\WxPay;
use common\Config;
use common\models\Common;
use common\models\GoodsCartModel;
use common\models\GoodsLogisticsModel;
use common\models\GoodsModel;
use common\models\GoodsOrderModel;
use common\models\GoodsSpecificationModel;
use common\models\UserModel;
use common\models\UserXmobModel;
use common\models\ChannelCallbackModel;
use common\models\Jusheng;
use common\models\JushengVipModel;
use common\models\JushengVipOrderModel;
use common\models\TypeLogModel;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\GoodsOrderIndex;
use api\models\GoodsOrderView;
use common\models\GoodsOrderSubModel;
use common\models\XiaoyingVipOrderModel;

class GoodsOrderController extends CommonController
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
            'except' => ['index', 'view', 'zfb-async', 'yibu']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    public function actionPostZfb() // 支付宝支付老版本 兼容之前 现已废弃
    {
        $order_sign = Common::genOrderSign();
        $user_id = $this->userId;
        $goods_id = $this->post('goods_id');
        $user_name = $this->post('user_name');
        $mobile = $this->post('mobile');
        $receive_addr = $this->post('receive_addr');
        $specification_id = $this->post('specification_id'); // 规格id
        $number = $this->post('number') ?? 1;
        $type = $this->post('type'); // original_click    share_click
        $remarks = $this->post('remarks') ?? ''; //订单备注

        if (!$goods_id) return Common::response(0, 'failure', '未知产品信息');
        if (!$specification_id) return Common::response(0, 'failure', '未知规格信息');
        if (!$user_name) return Common::response(0, 'failure', '请填写收货人的名称');
        if (!$mobile) return Common::response(0, 'failure', '请填写收货人手机号');
        if (!$receive_addr) return Common::response(0, 'failure', '请填写收货人的地址信息');
        if ($type !== 'original_click' && $type !== 'share_click') return Common::response(0, 'failure', '类型参数不正确');
        if (!is_numeric($number) || $number < 1) return Common::response(0, 'failure', '非法的购买数量参数');

        $goods_info = GoodsModel::find()->where(['id' => $goods_id])->asArray()->one();
        if (!$goods_info) return Common::response(0, 'failure', '未找到对应商品');
        if ($goods_info['status'] != 1) return Common::response(0, 'failure', '该商品已下架');

        $subject = $goods_info['name'];
        $service = $goods_info['service'];
        $image = $goods_info['image'];
        $logistics_fee = $goods_info['logistics_fee'];

        $specification_info = GoodsSpecificationModel::find()->select('name,original_cost,after_discount_cost')->where(['id' => $specification_id])->one();
        $specification = $specification_info['name'];
        $can_discount = Common::checkShopDiscount($this->userId, $goods_id);
        if (!$can_discount && $type == 'share_click') return Common::response(0, 'failure', '非法操作');

        if ($type == 'share_click') {  // 分享价
            $price = $specification_info['after_discount_cost']; // 单价
            $price_total = $price * $number;
        } else {
            $price = $specification_info['original_cost']; // 单价
            $price_total = $price * $number;
        }
        $order_total = $price_total + $logistics_fee;

        $params = [
            'subject' => $subject,
            'order_sign' => $order_sign, // 商家订单号
            'price_total' => $order_total  // 订单总金额,加上运费的
        ];

        $ali = new AliPay();
        $order_str = $ali->createOrder($params, 'goods');

        $attributes = [
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            'user_name' => $user_name,
            'mobile' => $mobile,
            'receive_addr' => $receive_addr,
            'status' => Config::SHOP_ORDER_UNPAY,   // 待付款
            'order_sign' => $order_sign,
            'goods_name' => $subject,
            'goods_price' => $price,
            'goods_price_total' => $price_total,  // 总价不包含运费
            'goods_specification_id' => $specification_id,
            'logistics_fee' => $logistics_fee,
            'goods_specification_name' => $specification,
            'goods_service' => $service,
            'goods_image' => $image,
            'create_time' => date('Y-m-d H:i:s'),
            'order_detail' => $order_str,
            'auto_cancel_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'number' => $number,
            'remarks' => $remarks,
            'channel' => 'zfb',
        ];

        $model = new GoodsOrderModel();
        $model->setAttributes($attributes);
        if ($model->save()) {
            try {
                // 待支付自动取消
                $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
                $client->connect('127.0.0.1', 9510);
                $data = $model->id;
                $data = pack('N', strlen($data)) . $data;
                $client->send($data);
            } catch (\Exception $e) {
                // pass
            } catch (\Throwable $e) {
                // pass
            }
            return Common::response(1, 'success', ['order_id' => (string)$model->id, 'order' => $order_str]);
        }
        return Common::response(0, 'err', $model->getErrors());
    }

    /*
     * 支付宝异步通知
     * */
    public function actionZfbAsync()
    {
        $post_data = $this->post();
        if (isset($post_data['pay_version'])) {
            $pay_version = $post_data['pay_version'];
            unset($post_data['pay_version']);
        } else {
            $pay_version = '';
        }

        $log = new TypeLogModel();
        $log->create_time = date('Y-m-d H:i:s');
        $log->content = json_encode($post_data);
        $log->type = Config::LOG_ZFB_SHOP_NOTIFY;
        $log->save();

        /*
         * 支付宝验签
         * */
        $ali = new AliPay($pay_version);
        try {
            $flag = $ali->checkSign($post_data);
        } catch (\Exception $e) {
            $flag = false;
        }
        if ($flag !== true) return false;

        $time = $post_data['notify_time'] ?? date('Y-m-d H:i:s');
        $order_sign = $post_data['out_trade_no'] ?? '';
        $trade_status = $post_data['trade_status'] ?? ''; // 交易状态
        $type = $post_data['passback_params'] ?? ''; // 购买的类型
        if ($trade_status == 'TRADE_SUCCESS') {  // 支付成功
            if ($type == 'goods') {  // 商品  跳转成已付款
                $model = GoodsOrderModel::find()->where(['order_sign' => $order_sign, 'status' => Config::SHOP_ORDER_UNPAY])->one();
                if ($model) {
                    $model->status = Config::SHOP_ORDER_HAS_PAY; // 等待发货 已付款
                    $model->update_time = $time;
                    $model->save();
                    $this->Buyback($model, $order_sign, $type);
                    die("success");
                }
            } elseif ($type == 'interests_month') { // 犀京金月卡会员
                $model = JushengVipOrderModel::find()->where(['order_sign' => $order_sign])->one();
                $model->trade_status = 1;
                $model->update_time = $time;
                $model->save();
                $user_id = $model->user_id;
                $vip_model = JushengVipModel::find()->where(['user_id' => $user_id])->one();
                if (!$vip_model) {  // 不存在添加
                    $expire_time = date('Y-m-d 23:59:59', strtotime("+30 day"));
                    $vip_model = new JushengVipModel();
                    $vip_model->user_id = $user_id;
                    $vip_model->vip_type = 3; // 月卡会员
                    $vip_model->user_id = $user_id;
                    $vip_model->expire_time = $expire_time;
                    $vip_model->create_time = date('Y-m-d H:i:s');
                    $vip_model->save();
                } else {
                    // 存在已过期
                    $expire_time = $vip_model->expire_time;
                    if (strtotime($expire_time) <= time()) { // 已经过期
                        $expire_time = date('Y-m-d 23:59:59', strtotime("+30 day"));
                        $vip_model->expire_time = $expire_time;
                        $vip_model->update_time = date('Y-m-d H:i:s');
                        $vip_model->save();

                    } else { // 还未过期
                        $expire_time = date('Y-m-d 23:59:59', strtotime("+30 day", strtotime($expire_time))); // 续费
                        $vip_model->expire_time = $expire_time;
                        $vip_model->update_time = date('Y-m-d H:i:s');
                        $vip_model->save();
                    }
                }
                $jusheng = new Jusheng();
                $data = [
                    'userId' => $user_id,
                    'vipType' => '1',
                    'vipExpireTime' => $expire_time
                ];
                $data = $jusheng->getData('/appweb/data/ws/rest/vip/agent/customer/push', $data);  // 无状态调用
                $jusheng_vip_id = $data['userId'];
                $vip_model->jusheng_vip_id = $jusheng_vip_id;  // 巨省会员id
                $vip_model->save();
                $this->Buyback($model, $order_sign, $type);
                die("success");
            } elseif ($type == 'interests_quarter') { // 犀京季卡会员
                $model = JushengVipOrderModel::find()->where(['order_sign' => $order_sign])->one();
                $model->trade_status = 1;
                $model->update_time = $time;
                $model->save();
                $user_id = $model->user_id;
                $vip_model = JushengVipModel::find()->where(['user_id' => $user_id])->one();
                if (!$vip_model) {  // 不存在添加
                    $expire_time = date('Y-m-d 23:59:59', strtotime("+90 day"));
                    $vip_model = new JushengVipModel();
                    $vip_model->user_id = $user_id;
                    $vip_model->vip_type = 1; // 季度会员
                    $vip_model->user_id = $user_id;
                    $vip_model->expire_time = $expire_time;
                    $vip_model->create_time = date('Y-m-d H:i:s');
                    $vip_model->save();
                } else {
                    // 存在
                    $expire_time = $vip_model->expire_time;
                    if (strtotime($expire_time) <= time()) { // 已经过期
                        $expire_time = date('Y-m-d 23:59:59', strtotime("+90 day"));
                        $vip_model->expire_time = $expire_time;
                        $vip_model->update_time = date('Y-m-d H:i:s');
                        $vip_model->save();

                    } else { // 还未过期
                        $expire_time = date('Y-m-d 23:59:59', strtotime("+90 day", strtotime($expire_time))); // 续费
                        $vip_model->expire_time = $expire_time;
                        $vip_model->update_time = date('Y-m-d H:i:s');
                        $vip_model->save();
                    }
                }
                $jusheng = new Jusheng();
                $data = [
                    'userId' => $user_id,
                    'vipType' => '1',
                    'vipExpireTime' => $expire_time
                ];
                $data = $jusheng->getData('/appweb/data/ws/rest/vip/agent/customer/push', $data);  // 无状态调用
                $jusheng_vip_id = $data['userId'];
                $vip_model->jusheng_vip_id = $jusheng_vip_id;  // 巨省会员id
                $vip_model->save();
                $this->Buyback($model, $order_sign, $type);
                die("success");
            } elseif ($type == 'interests_forever') { // 犀京终生会员
                $model = JushengVipOrderModel::find()->where(['order_sign' => $order_sign])->one();
                $model->trade_status = 1;
                $model->update_time = $time;
                $model->save();
                $user_id = $model->user_id;
                $vip_model = JushengVipModel::find()->where(['user_id' => $user_id])->one();
                $expire_time = date('Y-m-d 23:59:59', strtotime("+100 year"));
                if (!$vip_model) {  // 新增
                    $vip_model = new JushengVipModel();
                    $vip_model->user_id = $user_id;
                    $vip_model->vip_type = 2; // 终生会员
                    $vip_model->user_id = $user_id;
                    $vip_model->expire_time = $expire_time;
                    $vip_model->create_time = date('Y-m-d H:i:s');
                    $vip_model->save();
                } else {  // 存在 之前买过季度会员
                    $expire_time = date('Y-m-d 23:59:59', strtotime("+100 year"));
                    $vip_model->vip_type = 2;
                    $vip_model->expire_time = $expire_time;
                    $vip_model->update_time = date('Y-m-d H:i:s');
                    $vip_model->save();
                }
                $jusheng = new Jusheng();
                $data = [
                    'userId' => $user_id,
                    'vipType' => '1',
                    'vipExpireTime' => $expire_time
                ];
                $data = $jusheng->getData('/appweb/data/ws/rest/vip/agent/customer/push', $data);
                $jusheng_vip_id = $data['userId'];
                $vip_model->jusheng_vip_id = $jusheng_vip_id;  // 巨省会员id
                $vip_model->save();
                // 生成劵
                Common::genCoupon($user_id);
                $this->Buyback($model, $order_sign, $type);
                die("success");
            } elseif ($type == 'interests_xiaoying') { // 贷超解锁会员
                $model = XiaoyingVipOrderModel::find()->where(['order_sign' => $order_sign])->one();
                $model->trade_status = 1;
                $model->update_time = $time;
                $model->save();
                die("success");
            }
        } elseif ($trade_status == 'TRADE_CLOSED') { // 退款成功
            die("success");
        }
        die("retry");
    }

    //微信支付成功后接收异步通知并回调微信官方
    public function actionYibu()
    {
        $content = file_get_contents("php://input");
        $jsonxml = json_encode(simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA));
        $result = json_decode($jsonxml, true);//转成数组，
        if (is_array($result)) {
            file_put_contents(Yii::$app->getRuntimePath() . "/logs/wx_huidiao.log", implode(',', $result) . PHP_EOL, FILE_APPEND);
        }
        //$result = json_decode(json_encode(simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        //退款结果回调解密：openssl_decrypt(base64_decode($str),"AES-128-ECB",$this->key,OPENSSL_RAW_DATA);
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            if (isset($result['pay_version'])) {
                $pay_version = $result['pay_version'];
                unset($result['pay_version']);
            } else {
                $pay_version = '';
            }
            $wx = new WxPay($pay_version);
            $res = $wx->notify($result['out_trade_no']);
            //签名验证通过后期处理
            if ($res['trade_state'] == 'SUCCESS') {
                $log = new TypeLogModel();
                $log->create_time = date('Y-m-d H:i:s');
                $log->content = json_encode($result);
                $log->type = Config::LOG_WX_SHOP_NOTIFY;
                $log->save(false);
                //交易支付成功
                $time = date("Y-m-d H:i:s");
                file_put_contents(Yii::$app->getRuntimePath() . "/logs/wx_success.log", $time . ',' . $result['out_trade_no'] . ',' . $result['total_fee'] . ',' . implode(',', $result) . PHP_EOL, FILE_APPEND);
                $model = GoodsOrderModel::find()->where(['order_sign' => $result['out_trade_no'], 'status' => Config::SHOP_ORDER_UNPAY])->one();
                if ($model) {
                    //订单处理
                    $time = date('Y-m-d H:i:s', strtotime($result['time_end']));
                    $model->status = Config::SHOP_ORDER_HAS_PAY; // 等待发货 已付款
                    $model->update_time = $time;
                    $model->order_detail = 'out_trade_no:' . $result['out_trade_no'] . 'trade_no:' . $result['transaction_id'] . 'price:' . $result['total_fee'] / 100;
                    $model->save();
                    $this->Buyback($model,$result['out_trade_no'],'goods');
                    $info = array(
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK'
                    );
                    //异步返回给微信成功参数
                    $formData = $wx->arrayToXml($info);
                    return $formData;
                }
                $quarter_model = JushengVipOrderModel::find()->where(['order_sign' => $result['out_trade_no'],'vip_type' => 1])->one();
                if ($quarter_model){ // 犀京季卡会员订单
                    $quarter_model->trade_status = 1;
                    $quarter_model->update_time = $time;
                    $quarter_model->save();
                    $user_id = $quarter_model->user_id;
                    $vip_model = JushengVipModel::find()->where(['user_id' => $user_id])->one();
                    if (!$vip_model) {  // 不存在添加
                        $expire_time = date('Y-m-d 23:59:59', strtotime("+90 day"));
                        $vip_model = new JushengVipModel();
                        $vip_model->user_id = $user_id;
                        $vip_model->vip_type = 1; // 季度会员
                        $vip_model->user_id = $user_id;
                        $vip_model->expire_time = $expire_time;
                        $vip_model->create_time = date('Y-m-d H:i:s');
                        $vip_model->save();
                    } else {
                        // 存在
                        $expire_time = $vip_model->expire_time;
                        if (strtotime($expire_time) <= time()) { // 已经过期
                            $expire_time = date('Y-m-d 23:59:59', strtotime("+90 day"));
                            $vip_model->expire_time = $expire_time;
                            $vip_model->update_time = date('Y-m-d H:i:s');
                            $vip_model->save();

                        } else { // 还未过期
                            $expire_time = date('Y-m-d 23:59:59', strtotime("+90 day", strtotime($expire_time))); // 续费
                            $vip_model->expire_time = $expire_time;
                            $vip_model->update_time = date('Y-m-d H:i:s');
                            $vip_model->save();
                        }
                    }
                    $jusheng = new Jusheng();
                    $data = [
                        'userId' => $user_id,
                        'vipType' => '1',
                        'vipExpireTime' => $expire_time
                    ];
                    $data = $jusheng->getData('/appweb/data/ws/rest/vip/agent/customer/push', $data);  // 无状态调用
                    $jusheng_vip_id = $data['userId'];
                    $vip_model->jusheng_vip_id = $jusheng_vip_id;  // 巨省会员id
                    $vip_model->save();
                    $this->Buyback($quarter_model,$result['out_trade_no'],'');
                    $info = array(
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK'
                    );
                    //异步返回给微信成功参数
                    $formData = $wx->arrayToXml($info);
                    return $formData;
                }
                $forever_model = JushengVipOrderModel::find()->where(['order_sign' => $result['out_trade_no'],'vip_type' => 2])->one();
                if ($forever_model){ // 犀京终生卡会员订单
                    $forever_model->trade_status = 1;
                    $forever_model->update_time = $time;
                    $forever_model->save();
                    $user_id = $forever_model->user_id;
                    $vip_model = JushengVipModel::find()->where(['user_id' => $user_id])->one();
                    $expire_time = date('Y-m-d 23:59:59', strtotime("+100 year"));
                    if (!$vip_model) {  // 新增
                        $vip_model = new JushengVipModel();
                        $vip_model->user_id = $user_id;
                        $vip_model->vip_type = 2; // 终生会员
                        $vip_model->user_id = $user_id;
                        $vip_model->expire_time = $expire_time;
                        $vip_model->create_time = date('Y-m-d H:i:s');
                        $vip_model->save();
                    } else {  // 存在 之前买过季度会员
                        $expire_time = date('Y-m-d 23:59:59', strtotime("+100 year"));
                        $vip_model->vip_type = 2;
                        $vip_model->expire_time = $expire_time;
                        $vip_model->update_time = date('Y-m-d H:i:s');
                        $vip_model->save();
                    }
                    $jusheng = new Jusheng();
                    $data = [
                        'userId' => $user_id,
                        'vipType' => '1',
                        'vipExpireTime' => $expire_time
                    ];
                    $data = $jusheng->getData('/appweb/data/ws/rest/vip/agent/customer/push', $data);
                    $jusheng_vip_id = $data['userId'];
                    $vip_model->jusheng_vip_id = $jusheng_vip_id;  // 巨省会员id
                    $vip_model->save();
                    // 生成劵
                    Common::genCoupon($user_id);
                    $this->Buyback($forever_model,$result['out_trade_no'],'');
                    $info = array(
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK'
                    );
                    //异步返回给微信成功参数
                    $formData = $wx->arrayToXml($info);
                    return $formData;
                }
                $month_model = JushengVipOrderModel::find()->where(['order_sign' => $result['out_trade_no'],'vip_type' => 3])->one();
                if ($month_model){ // 犀京月卡会员订单
                    $month_model->trade_status = 1;
                    $month_model->update_time = $time;
                    $month_model->save();
                    $user_id = $month_model->user_id;
                    $vip_model = JushengVipModel::find()->where(['user_id' => $user_id])->one();
                    if (!$vip_model) {  // 不存在添加
                        $expire_time = date('Y-m-d 23:59:59', strtotime("+30 day"));
                        $vip_model = new JushengVipModel();
                        $vip_model->user_id = $user_id;
                        $vip_model->vip_type = 3; // 月卡会员
                        $vip_model->user_id = $user_id;
                        $vip_model->expire_time = $expire_time;
                        $vip_model->create_time = date('Y-m-d H:i:s');
                        $vip_model->save();
                    } else {
                        // 存在已过期
                        $expire_time = $vip_model->expire_time;
                        if (strtotime($expire_time) <= time()) { // 已经过期
                            $expire_time = date('Y-m-d 23:59:59', strtotime("+30 day"));
                            $vip_model->expire_time = $expire_time;
                            $vip_model->update_time = date('Y-m-d H:i:s');
                            $vip_model->save();

                        } else { // 还未过期
                            $expire_time = date('Y-m-d 23:59:59', strtotime("+30 day", strtotime($expire_time))); // 续费
                            $vip_model->expire_time = $expire_time;
                            $vip_model->update_time = date('Y-m-d H:i:s');
                            $vip_model->save();
                        }
                    }
                    $jusheng = new Jusheng();
                    $data = [
                        'userId' => $user_id,
                        'vipType' => '1',
                        'vipExpireTime' => $expire_time
                    ];
                    $data = $jusheng->getData('/appweb/data/ws/rest/vip/agent/customer/push', $data);  // 无状态调用
                    $jusheng_vip_id = $data['userId'];
                    $vip_model->jusheng_vip_id = $jusheng_vip_id;  // 巨省会员id
                    $vip_model->save();
                    $this->Buyback($month_model,$result['out_trade_no'],'');
                    $info = array(
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK'
                    );
                    //异步返回给微信成功参数
                    $formData = $wx->arrayToXml($info);
                    return $formData;
                }
                $xiaoying_model = XiaoyingVipOrderModel::find()->where(['order_sign' => $result['out_trade_no']])->one();
                if ($xiaoying_model){ // 啸鹰会员订单
                    $xiaoying_model->trade_status = 1;
                    $xiaoying_model->update_time = $time;
                    $xiaoying_model->save();
                    $this->Buyback($xiaoying_model,$result['out_trade_no'],'');
                    $info = array(
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK'
                    );
                    //异步返回给微信成功参数
                    $formData = $wx->arrayToXml($info);
                    return $formData;
                }
            } elseif($res['trade_state'] == 'REFUND') {
                return true;
            }
        } else {
            return false;
        }
    }


    /*
     * 订单列表
     * */
    public function actionIndex()
    {
        $status = $this->get('status');
        $query = GoodsOrderIndex::find()->where(['user_id' => $this->userId, 'is_del' => 0])->andFilterWhere(['status' => $status])->orderBy('id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /*
     * 订单详情页
     * */
    public function actionView($id)
    {
        $version = GoodsOrderModel::find()->select('version')->where(['id' => $id])->scalar();
        if (empty($version)) {
            $query = GoodsOrderView::find()->where(['id' => $id, 'user_id' => $this->userId, 'is_del' => 0]);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
            ]);

            return $dataProvider;
        } elseif ($version == 'v2') {
            $query = GoodsOrderViewV2::find()->where(['id' => $id, 'user_id' => $this->userId, 'is_del' => 0]);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
            ]);
            return $dataProvider;
        }

    }

    /*
     * 未付款状态 取消订单
     * */
    public function actionCancel()
    {
        $order_id = $this->post('order_id');
        $model = GoodsOrderModel::find()->where(['id' => $order_id, 'user_id' => $this->userId, 'status' => Config::SHOP_ORDER_UNPAY])->one();
        if (!$model) return Common::response(0, '未找到该对象');
        if(!empty($model->coupon_code)){
            $coupon = CouponUserModel::find()->where(['coupon_code' => $model->coupon_code, 'user_id' => $this->userId, 'coupon_status' => 3])->one();
            if($coupon){
                $data['coupon_status'] = 0;
                $coupon->setAttributes($data);
                $coupon->save(false);
            }
        }
        $model->status = Config::SHOP_ORDER_HAS_CANCELED;
        $model->update_time = date('Y-m-d H:i:s');
        if ($model->save()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }

    /*
     * 待发货状态  申请退款
     * */
    public function actionApplyForRefund()
    {
        $order_id = $this->post('order_id');
        $model = GoodsOrderModel::find()->where(['id' => $order_id, 'user_id' => $this->userId, 'status' => Config::SHOP_ORDER_HAS_PAY])->one();
        if (!$model) return Common::response(0, '未找到该对象');
        $model->apply_for_refund = Config::APPLY_FOR_REFUND_DOING;
        if ($model->save()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }

    /*
     * 待收货状态  确认收货
     * */
    public function actionConfirmRecv()
    {
        $order_id = $this->post('order_id');
        $model = GoodsOrderModel::find()->where(['id' => $order_id, 'user_id' => $this->userId, 'status' => Config::SHOP_ORDER_HAS_DELIVERED])->one();

        if (!$model) return Common::response(0, '未找到该对象');
        $transaction = GoodsOrderModel::getDb()->beginTransaction();
        try {
            $model->status = Config::SHOP_ORDER_HAS_FINISHED;
            $model->update_time = date('Y-m-d H:i:s');
            $model->save();

            $goods_logistics = GoodsLogisticsModel::find()->where(['goods_order_id' => $order_id])->one();
            if ($goods_logistics) { // 一般情况一定有 脏数据的话会让事务提交失败 兼容
                $goods_logistics->receive_time = date('Y-m-d H:i:s');
                $goods_logistics->save();
            }

            $transaction->commit();
            return Common::response(1, 'success');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        }
    }

    /*
     * 已取消/已完成的状态  订单删除
     * */
    public function actionDelOrder()
    {
        $order_id = $this->post('order_id');
        $model = GoodsOrderModel::find()->where(['id' => $order_id, 'user_id' => $this->userId, 'is_del' => 0])
            ->andWhere(['or', ['=', 'status', Config::SHOP_ORDER_HAS_FINISHED], ['=', 'status', CONFIG::SHOP_ORDER_HAS_CANCELED]])
            ->one();
        if (!$model) return Common::response(0, '未找到该对象');
        $model->is_del = 1;
        $model->update_time = date('Y-m-d H:i:s');
        if ($model->save()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }


    // 新版订单
    public function actionCreateOrder()
    {
        $is_ios = $this->get('ios');
        $order_sign = Common::genOrderSign();
        $user_id = $this->userId;

        /*
         * 公有的
         * */
        $version = 'v2';
        $receive_addr = $this->post('receive_addr');
        $mobile = $this->post('mobile');
        $user_name = $this->post('user_name');
        $buy_type = $this->post('buy_type'); // zfb wx
        $pay_version = $this->post('pay_version') ?? '';
        $coupon_code = $this->post('coupon_code') ?? '';
        $remarks = $this->post('remarks') ?? ''; //订单备注

        if (!$user_name) return Common::response(0, 'failure', '请填写收货人的名称');
        if (!$mobile) return Common::response(0, 'failure', '请填写收货人手机号');
        if (!$receive_addr) return Common::response(0, 'failure', '请填写收货人的地址信息');
        if ($buy_type !== 'zfb' && $buy_type !== 'wx') return Common::response(0, 'failure', '请填写正确的支付类型');

        $can_discount = Common::checkShopDiscount($this->userId, null);

        if ($is_ios) {
            $shopping_goods = json_decode($this->post('shopping_goods'), true);
        } else {
            $shopping_goods = $this->post('shopping_goods');
        }
        list($order_subject, $order_price_total) = $this->getOrderInfo($shopping_goods, $can_discount);
        if(!empty($coupon_code)){
            $time = date('Y-m-d H:i:s');
            $coupon = CouponUserModel::find()
                ->alias('a')
                ->select('b.amount,b.limit_amount,b.coupon_code')
                ->leftJoin('coupon b', 'a.coupon_code = b.coupon_code')
                ->where(['a.coupon_code' => $coupon_code, 'a.coupon_status' => 0])
                ->andwhere(['<', 'a.start_time', $time])
                ->andWhere(['>', 'a.end_time', $time])
                ->asArray()
                ->one();
            if($coupon){
                $order_price_total = $order_price_total - (float)$coupon['amount'];
                $coupon_model = CouponUserModel::find()->where(['coupon_code' => $coupon['coupon_code'],'user_id'=>$this->userId])->one();
                if($coupon_model){
                    $data['coupon_status'] = 3;
                    $coupon_model->setAttributes($data);
                    $coupon_model->save(false);
                }
            }
        }
        $params = [
            'subject' => $order_subject,
            'order_sign' => $order_sign, // 商家订单号
            'price_total' => $order_price_total  // 订单总金额
        ];

        if ($buy_type == 'zfb') {
            $ali = new AliPay($pay_version);
            $order_str = $ali->createOrder($params, 'goods');
        }
        if ($buy_type == 'wx') {
            $wx = new WxPay($pay_version);
            $order_str = $wx->wechat_pay(mb_substr($order_subject,0,20), $order_sign, $order_price_total, 'APP');
        }

        $attributes = [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'mobile' => $mobile,
            'receive_addr' => $receive_addr,
            'status' => Config::SHOP_ORDER_UNPAY,   // 待付款
            'order_sign' => $order_sign,
            'goods_price_total' => $order_price_total,  // 商品真正总价
            'create_time' => date('Y-m-d H:i:s'),
            'order_detail' => $order_str,
            'auto_cancel_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'channel' => $buy_type,
            'version' => $version,
            'remarks' => $remarks,
            'pay_version' => $pay_version,
            'coupon_code' => $coupon_code,
            'coupon_sta' => 0
        ];

        $transaction = GoodsOrderModel::getDb()->beginTransaction();

        try {
            $model = new GoodsOrderModel();
            $model->setAttributes($attributes);
            $model->save();

            //$model->getErrors();exit();

            foreach ($shopping_goods as $k => $v) {
                $goods_id = $v['goods_id'];
                $specification_id = $v['specification_id'];
                $number = $v['number'] ?? 1;

                if (!$goods_id) return Common::response(0, 'failure', '未知产品信息');
                if (!$specification_id) return Common::response(0, 'failure', '未知规格信息');
                if (!is_numeric($number) || $number < 1) return Common::response(0, 'failure', '非法的购买数量参数');

                $goods_info = GoodsModel::find()->where(['id' => $goods_id])->asArray()->one();
                if (!$goods_info) return Common::response(0, 'failure', '未找到对应商品');
                if ($goods_info['status'] != 1) return Common::response(0, 'failure', '该商品已下架');

                $subject = $goods_info['name'];
                $service = $goods_info['service'];
                $image = $goods_info['image'];
                $logistics_fee = $goods_info['logistics_fee'];

                $specification_info = GoodsSpecificationModel::find()->select('name,original_cost,after_discount_cost')->where(['id' => $specification_id])->one();
                $specification = $specification_info['name'];

                if ($can_discount) {
                    $price = $specification_info['after_discount_cost'];
                    $price_total = $price * $number;
                } else {
                    $price = $specification_info['original_cost'];
                    $price_total = $price * $number;  // 总价不包含运费
                }

                $sub_order_model = new GoodsOrderSubModel();
                $sub_order_model->goods_order_id = $model->id;
                $sub_order_model->goods_id = $goods_id;
                $sub_order_model->goods_name = $subject;
                $sub_order_model->goods_specification_id = $specification_id;
                $sub_order_model->goods_specification_name = $specification;
                $sub_order_model->goods_image = $image;
                $sub_order_model->goods_price = $price;
                $sub_order_model->goods_price_total = $price_total;  // 不包含运费的货品乘积数
                $sub_order_model->logistics_fee = $logistics_fee;
                $sub_order_model->goods_service = $service;
                $sub_order_model->number = $number;
                $sub_order_model->save();
            }

            $specification_ids = array_column($shopping_goods, 'specification_id');

            $cart_info = GoodsCartModel::find()->where(['u_id' => $this->userId])->one();
            if (!empty($cart_info)) {
                $cart_list = $cart_info->data;
                $flag = false;
                foreach ($cart_list as $k => $v) {
                    if (in_array($k, $specification_ids)) {
                        unset($cart_list[$k]);
                        $flag = true;
                    }
                }
                if ($flag) {
                    $cart_info->data = $cart_list;
                    $cart_info->save();
                }
            }

            $transaction->commit();
            try {
                // 待支付自动取消
                $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
                $client->connect('127.0.0.1', 9510);
                $data = $model->id;
                $data = pack('N', strlen($data)) . $data;
                $client->send($data);
            } catch (\Exception $e) {
                // pass
            } catch (\Throwable $e) {
                // pass
            }
            return Common::response(1, 'success', ['order_id' => (string)$model->id, 'order' => $order_str]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        }

    }

    // 得到订单总价值和商品名
    private function getOrderInfo($shopping_goods, $can_discount)
    {
        $price_total = 0;
        $i = 0;
        foreach ($shopping_goods as $k => $v) {
            $goods_info = GoodsModel::find()->select('name,logistics_fee')->where(['id' => $v['goods_id']])->one();  // 无语
            $logistics_fee = $goods_info['logistics_fee'] ?? 0;
            if (++$i === 1) {
                $subject = $goods_info['name'];
            }
            if ($can_discount) {
                $price = GoodsSpecificationModel::find()->select('after_discount_cost')->where(['id' => $v['specification_id']])->scalar() ?: 0;
            } else {
                $price = GoodsSpecificationModel::find()->select('original_cost')->where(['id' => $v['specification_id']])->scalar() ?: 0;
            }
            $here_price = $price * $v['number'];
            $price_total += $here_price;
            $price_total += $logistics_fee;
        }

        return [
            $subject,
            $price_total
        ];
    }

    // 第三方支付回调
    private function Buyback($model, $order_sign, $type)
    {
        if ($model['user_id']) {
            $user = UserModel::find()->where(['id' => $model['user_id']])->one();
            if ($user) {
                //XMOB付款回调
                $xmob = UserXmobModel::find()->where(['mobile' => $user['mobile']])->andWhere(['active' => 1])->one();
                if ($xmob) {
                    if ($type == 'goods') {
                        UserXmobModel::updateAll(['g_buy' => $order_sign,'active'=>2], ['mobile' => $user['mobile']]);
                    } else {
                        UserXmobModel::updateAll(['q_buy' => $order_sign,'active'=>2], ['mobile' => $user['mobile']]);
                    }
                    $xmob = UserXmobModel::find()->where(['mobile' => $user['mobile']])->one();
                    $mob_cid = $xmob['mob_cid'];
                    $plid = $xmob['plid'];
                    $backtype = 2;
                    //XMOB激活回调url
                    $url = 'https://www.lnkdata.com/UrlCenter/Osh?menu_id=800003&mob_cid=' . $mob_cid . '&plid=' . $plid . '&type=' . $backtype;
                    file_get_contents($url);
                }
                $toutiao = ChannelCallbackModel::find()->where(['user_mobile' => $user['mobile']])->andWhere(['type' => 'toutiao'])->andWhere(['status' => 1])->one();
                if ($toutiao) {
                    $toutiao->status = 2;
                    $toutiao->update_time = date('Y-m-d H:i:s');
                    $toutiao->save(false);
                    $event_type = 2;
                    //回调url
                    $url = $toutiao['callback_url'] . '&androidid=' . $toutiao['androidid'] . '&event_type=' . $event_type;
                    file_get_contents($url);
                }
            }
        }

    }

}
