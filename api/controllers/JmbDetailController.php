<?php

namespace api\controllers;

use common\models\JmbModel;
use common\models\JmbUserModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\JmbDetailModel;
use common\aop\AopClient;
use common\models\JmbOrderModel;
use common\models\Common;
use common\models\JmbNotifyLogModel;

class JmbDetailController extends CommonController
{
    CONST PERIOD = [
        0 => '永久'
    ];

    CONST TYPE = [
        0 => '电话接通'
    ];

    public $order_params = [
        'appId' => '2021001107613197',
        'rsaPrivateKey' => 'MIIEpAIBAAKCAQEAsfQ37GByu5sjUpFkH0/aptzKiO91PAPBFKAf5acKNIwM5CW4PkiiLk2wNGUpxvfq6fvQ3Ne+6NIp96syjOYgIS4vBpgr+P34bG5h4+maRTLfxCp+g85Fx3Mx7qp+/ZLNjIwoPZqeEqUlCU6hvC0x2wiMGQbx3h4CFKc2L6n2wD6XacT8HEhNdX0yj6VINQ6mYunD9WCLXp4a8r1NdzfaEGGPnuNpFexfhD9I/iHFgVns3VeNR1MLQlWEiUZ/kX5vbYa09W0RlKvdk+kwJs089NG2wJeAVbleHrkz4s9QOTn363GamRIeOk7wKxvh9GodsVbcZQgHEdKMJbnWmAWOtwIDAQABAoIBAH8OJ3+VvVVFhwrE0/+nPC43JkRu8/1NkOXxDdUiVp79/5ZFeC7AHUuCHHTMZe+xwwAc0xtazuvcYip7CTSgegA2wUysCXlVm7GPfkUD3SnbEPk2fe4NsLSfAt+sy86wqiHXUEPryUD2uGLUyZVZj2cbyytzUe2M719fo8iUSaEn3cfGOgt/t+fteb29qsaZHVrsLiY04QZCSScjVhKQfe74rJOMgVgfQu7X8bEsfHdOSLvox5ll1I0ITh8PpyWXMMu23TD9yt7YGxCpl0Alpbm5y0/YJRr+BKV3JPh4zyM/JIoSanejmJjgXGRI+JDkq+tyYX7fcijhNu4GgBDKAQECgYEA5bSQz72PyTivExYlnjNxAN+grH2qylHaKuyde01sBzMcgxSxxmTIJOnHGUaURIiaF+kmPXQGyN41Jf6iX5VHVc3ScD839J+lbtPRjMwYOa7tjLjrDsJOlA9/Wa9D191JiSvM1BPMB2NIyTtMfNt4KL5JOQkHiBJvezGJCn6OwmcCgYEAxlMY8IeL/FbcjqwCqM0d0mWaMC7+lqpE1osGDofzCYihNJCIy4CZbGDPodu3GLH2D3W89PNk3kcU4oGMrM1vqErLuSLHq+wfuF09BD9ec3RJxCwoR83JawUNEG6k25pn4IoIisQMRSp8FBdNruyUT3YavdUPPYTGHVgr0XFtPzECgYEA3IE5gjK2lLOVXP0ln3hCQlRqKSt5iD6K8Cm0A2mPClpGRI7ZGVolx0mjlt/p9OAtDEcELnnzCWBGJPMLbDYhBH1QRfBqBqo6ZOlxf3dsyfLilvPssX5VcExwM+fLeK00qjkhp3RZp73nYUVLZ5BHCNWh9N52HGmWGuyyYSCcaoECgYBMSUCJnQoKuXrDYoTvW18CRFTH/fuuBrbN89ITmtWwsB6aMpJ7/O6HEsIrV3GZjTLCBZ72df0xxxPQgNaUzb0KdiLhBvDZP81/xFWpIZr6PBv32qhv3keA5AN/c5/0XPNiGpZokjrKDCqIK+KUy6nM2vg0VxUyq7TtKrJIMSI9sQKBgQDQ9MdqjwqZCBG5FrK9SJeEDcCR3I0M3ZPp54jBhfJLG/uvb7SSLOy4CQqQLrc69aaoMJYvz04fFwQoPBGzsH7pT3eSoF8gFSD7tEj5OOvf9oJ1u7QwULbW+6fG3cK7CEY3sCkWfBsbwJix5MqkFm//80M3I+7FzfwuFuDKcoP4YQ==',
        'alipayrsaPublicKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkONhL/otLyTVbWdcutt1zpeUqGYr5l0B6/vbyhiJFBsrRnKVrI21zSZtylHutpc+z0InCLeDgQVNy2UVhHuhcfe/Trg1Eq5/a1+Fs7yK6OXJB0lTbPt8L37ACbqpUphooTmd2scsgPiPkOT42EoX4mQxBo1a5V/wgAmKfTz5SLq4b2PYtepMtmGdMWrXV/l9x15bMcaxgavf75NgKzVXxgKMCiQjIyJylSqkCIe7Nwn+96QAuks+NeJXpTWuFYF1q5XfaKvnym6XDXiUIOHc4PS+OZHEzKtYaZl1SCRVwK2lOqOlwNSlUsBZ7NBMng6jT7YbsfiUyJcFj9Egob7JVQIDAQAB',
//        'notifyUrl' => 'http://47.103.61.179:1022/jmb-detail/async-notify',
        'notifyUrl' => 'https://api.xykj1.com/jmb-detail/async-notify',
        'bizcontent' => "{\"subject\": \"@goods_name@\","
            . "\"out_trade_no\": \"@order_sign@\","
            . "\"total_amount\": \"@total_amount@\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}"
    ];

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
            'except' => ['index','async-notify']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionGet(){
        $jmb_id = $this->get('id');
        $is_vip = Common::checkVip($this->userId);
        $data = JmbDetailModel::find()->select('origin_price,vip_price,period,type')->where(['jmb_id' => $jmb_id])->asArray()->one();
        $data['period'] = self::PERIOD[$data['period']];
        $data['contact_way'] = self::TYPE[$data['type']];
        $data = array_merge(['is_vip' => $is_vip], $data);
        return $data;
    }


    public function actionBuy(){
        $jmb_id = $this->post('id');  // 加盟宝id
        if(!$jmb_id) return Common::response(0, 'id为空');
        $is_vip = Common::checkVip($this->userId);
        $subject = JmbModel::find()->select('name')->scalar();
        $subject .= '加盟方式';
        $price = JmbDetailModel::find()->select('origin_price,vip_price')->where(['jmb_id'=>$jmb_id])->asArray()->one();
        $price = $is_vip ? $price['vip_price'] : $price['origin_price'];
        $order_sign = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $bizcontent = $this->order_params['bizcontent'];
        $bizcontent = str_replace('@goods_name@',$subject,$bizcontent);
        $bizcontent = str_replace('@order_sign@',$order_sign,$bizcontent);
        $bizcontent = str_replace('@total_amount@',$price,$bizcontent);

        $aop = new AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->order_params['appId'];
        $aop->rsaPrivateKey = $this->order_params['rsaPrivateKey'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $this->order_params['alipayrsaPublicKey'];

        $request = new \common\aop\AlipayTradeAppPayRequest();
        $request->setNotifyUrl($this->order_params['notifyUrl']);
        $request->setBizContent($bizcontent);
        $str = $aop->sdkExecute($request);

        $save_data = [
            'jmb_id' => $jmb_id,
            'user_id' => $this->userId,
            'order_sign' => $order_sign,
            'create_time' => date('Y-m-d H:i:s'),
            'status' => 0
        ];
        $order_model = new JmbOrderModel;
        $order_model->setAttributes($save_data);
        if($order_model->save()) return Common::response(1, '订单生成成功', $str);
        return Common::response(0, '操作失败');
    }

    /*
     * 支付宝异步通知
     * */
    public function actionAsyncNotify(){
        $post_data = $this->post();
        $log_model = new JmbNotifyLogModel();
        $log_model->content = json_encode($post_data);
        $log_model->create_time = date('Y-m-d H:i:s');
        $log_model->save();
        /*
         * 支付宝验签
         * */
        try{
            $aop = new AopClient;
            $aop->alipayrsaPublicKey = $this->order_params['alipayrsaPublicKey'];
            $flag = $aop->rsaCheckV1($post_data, NULL, "RSA2");
            if($flag !== true) return false;
        }catch (\Exception $e){
            return false;
        }
        $log_model->status = 1;
        $log_model->save();
        
        $order_sign = $post_data['out_trade_no']; // 订单号
        $trade_status = $post_data['trade_status']; // 交易状态
        if($trade_status == 'TRADE_SUCCESS'){  // 支付成功
            $model = JmbOrderModel::find()->where(['order_sign' => $order_sign])->one();
            $transaction = JmbOrderModel::getDb()->beginTransaction();
            try {
                $model->status = 1;
                $model->update_time = date('Y-m-d H:i:s');
                $model->save();

                // 期限
                $period = JmbDetailModel::find()->select('period')->scalar() ?: 0;

                $jmb_user_model = new JmbUserModel();
                $jmb_user_model->jmb_id = $model->jmb_id;
                $jmb_user_model->user_id = $model->user_id;
                $jmb_user_model->period = $period;
                $jmb_user_model->create_time = date('Y-m-d H:i:s');
                $jmb_user_model->save();

                // 购买数量+1
                JmbModel::findOne($model->jmb_id)->updateCounters(['buy_num' => 1]);


                $transaction->commit();
            } catch(\Exception $e) {
                $transaction->rollBack();
                return false;
            } catch(\Throwable $e) {
                $transaction->rollBack();
                return false;
            }
        }
        return true;
    }
}
