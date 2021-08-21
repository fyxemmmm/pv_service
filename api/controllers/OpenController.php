<?php

namespace api\controllers;

use common\models\CouponModel;
use Yii;
use common\WxPay;
use common\Config;
use common\models\Common;
use common\models\Jusheng;
use common\models\ChannelCallbackModel;
use common\models\GoodsClickModel;
use common\models\GoodsModel;
use common\models\GoodsOrderModel;
use common\models\GoodsSpecificationModel;
use common\models\UserXmobModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;


class OpenController extends CommonController
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
            'except' => ['request-data', 'aiqiyi-register', 'weibo-register', 'tou-tiao-buy', 'tou-tiao', 'weibo', 'jushengid', 'yibu', 'weixin', 'toutiao-h5', 'ceshiyunpian', 'hui-chuan'],
        ];
        return $behaviors;
    }


    /*
     * 点击记录统计
     * type  1商品标识
     * */
    public function actionRequestData()
    {
        $get_data = $this->get();
        if (isset($get_data['type'])) {
            //type==1商品浏览统计
            if ($get_data['type'] == 1) {
                if (isset($get_data['user_id']) && isset($get_data['id'])) {
                    //商品浏览记录
                    $user_id = $get_data['user_id'];
                    $id = $get_data['id'];
                    $GoodsClick = new GoodsClickModel();
                    $time = time();
                    $GoodsClick->goods_id = (int)$id;
                    $GoodsClick->user_id = (int)$user_id;
                    $GoodsClick->create_time = $time;
                    $GoodsClick->save();
                    return "success!";
                } else {
                    return "参数错误";
                }
            }
        } else {
            return "TYPE参数错误";
        }
    }

    /*
     * 爱奇艺 注册
     * */
    public function actionAiqiyiRegister()
    {
        header('Content-Type:application/json; charset=UTF-8');
        $get = $this->get();
        $imei = $get['imei'] ?? '';
        $oaid = $get['oaid'] ?? '';
        $ad_id = $get['idfa'] ?? '';
        $openudid = $get['openudid'] ?? '';
        $os = $get['os'] ?? '';
        $callback = $get['callback_url'] ?? '';
        $androidid = $get['androidid'] ?? '';

        $model = new ChannelCallbackModel();
        $attr = [
            'imei' => $imei,
            'imeimd5' => $openudid,
            'oaid' => $oaid,
            'type' => 'aiqiyi',
            'ad_id' => $ad_id,
            'androidid' => $androidid,
            'ad_name' => $os,
            'callback_url' => $callback,
            'create_time' => date('Y-m-d H:i:s')
        ];
        $model->setAttributes($attr);
        if (!$model->save()) die(json_encode(['status' => 0]));
        die(json_encode(['status' => 1]));
    }

    /*
     * 微博 注册
     * */
    public function actionWeiboRegister()
    {
        header('Content-Type:application/json; charset=UTF-8');
        $get = $this->get();
        $campaignid = $get['campaignid'] ?? '';
        $idfa_md5 = $get['idfa_md5'] ?? '';
        $imei_md5 = $get['imei_md5'] ?? '';
        $oaid = $get['oaid'] ?? '';
        $os = $get['osversion'] ?? '';
        $androidid_md5 = $get['androidid_MD5'] ?? '';
        $imp = $get['IMP'] ?? '';

        $model = new ChannelCallbackModel();
        $attr = [
            'ad_id' => $campaignid,
            'imeimd5' => $imei_md5,
            'oaid' => $oaid,
            'androididmd5' => $androidid_md5,
            'type' => 'weibo',
            'imei' => $idfa_md5,
            'ad_name' => $os,
            'callback_url' => $imp,
            'create_time' => date('Y-m-d H:i:s')
        ];
        $model->setAttributes($attr);
        if (!$model->save()) die(json_encode(['status' => 0]));
        die(json_encode(['status' => 1]));
    }

    /*
     * 头条H5落地页
     * */
    public function actionToutiaoH5()
    {
        $mobile = $this->post('mobile');
        //广告计划id
        $aid = $this->post('adid');
        //广告创意id
        $cretiveid = $this->post('creativeid');
        //创意样式
        $creativetype = $this->post('creativetype');
        //标记每一次点击的唯一标识
        $clickid = $this->post('clickid');
        $posturl = $this->post('posturl');
        $posturl = urlencode($posturl);

        $arr = [
            'aid' => $aid,
            'creativetype' => $creativetype,
            'cretiveid' => $cretiveid
        ];
        //转化类型，注册3，激活19，会员购买2
        $type = 3;
        $xmob = UserXmobModel::find()->where(['mobile' => $mobile])->one();
        if (!$xmob) {
            $userxmob = new UserXmobModel();
            $time = date("Y-m-d H:i:s");
            $userxmob->mobile = $mobile;
            $userxmob->mob_cid = $clickid;
            $userxmob->plid = json_encode($arr);
            $userxmob->regist = $posturl;
            $userxmob->active = 0;
            $userxmob->type = 'toutiaoh5';
            $userxmob->create_time = $time;
            $userxmob->save();
            //回调url
            $url = 'https://ad.toutiao.com/track/activate/?link=' . $posturl . '&conv_time=' . time() . '&event_type=' . $type;
            //$url = 'https://www.lnkdata.com/UrlCenter/Osh?menu_id=800003&mob_cid='.$mob_cid.'&plid='.$plid.'&type='.$type;
            $file_contents = file_get_contents($url);
            return $file_contents;
        }
    }

    /*
     * 阿里汇川转化对接
     * */
    public function actionHuiChuan()
    {
        //https://api.xykj1.com/open/hui-chuan?idfa={IDFA1}&time={TS}&callback={CALLBACK_URL}&aid={AID}&cid={CID}
        //https://api.xykj1.com/open/hui-chuan?imei={IMEI_SUM1}&oaid={OAID}&time={TS}&callback={CALLBACK_URL}&androidid={ANDROIDID_SUM1}&aid={AID}&cid={CID}
        header('Content-Type:application/json; charset=UTF-8');
        $get = $this->get();
        $aid = $get['aid'] ?? '';
        $cid = $get['cid'] ?? '';
        $imei = $get['imei'] ?? '';
        $oaid = $get['oaid'] ?? '';
        $androidid = $get['androidid'] ?? '';
        $idfa = $get['idfa'] ?? '';
        $callback_url = $get['callback'] ?? '';
        file_put_contents(Yii::$app->getRuntimePath() . "/logs/ali_uc.log", 'mobile:' . $callback_url . 'aid:' . $aid . 'imei:' . $imei . 'oaid:' . $oaid . 'androidid:' . $androidid . 'cid:' . $cid . PHP_EOL, FILE_APPEND);
        if (!empty($imei)) {
            $toutiao = ChannelCallbackModel::find()->where(['imei' => $imei])->andWhere(['type' => 'uc'])->one();
        } else {
            if (!empty($oaid)) {
                $toutiao = ChannelCallbackModel::find()->where(['oaid' => $oaid])->andWhere(['type' => 'uc'])->one();
            } else {
                $toutiao = ChannelCallbackModel::find()->where(['androidid' => $androidid])->andWhere(['type' => 'uc'])->one();
            }
        }
        if (!$toutiao) {
            $model = new ChannelCallbackModel();
            $attr = [
                'imei' => $imei,
                'imeimd5' => '',
                'oaid' => $oaid,
                'androidid' => $androidid,
                'androididmd5' => $idfa,
                'type' => 'uc',
                'ad_id' => $aid,
                'ad_name' => $cid,
                'callback_url' => $callback_url,
                'create_time' => date('Y-m-d H:i:s')
            ];
            $model->setAttributes($attr);
            if ($model->save(false)) return 200;
            return 202;
        } else {
            $toutiao->imei = $imei;
            $toutiao->oaid = $oaid;
            $toutiao->androidid = $androidid;
            $toutiao->androididmd5 = $idfa;
            $toutiao->ad_id = $aid;
            $toutiao->ad_name = $cid;
            $toutiao->callback_url = $callback_url;
            $toutiao->save(false);
            if ($toutiao->save(false)) return 200;
            return 202;
        }
    }

    /*
     * 头条转化对接
     * */
    public function actionTouTiao()
    {
        //https://api.xykj1.com?adid=__AID__&cid=__CID__&idfa=__IDFA__&callback_url=__CALLBACK_URL__
        //https://api.xykj1.com?adid=__AID__&cid=__CID__&imei=__IMEI__&oaid=__OAID__&androidid=__ANDROIDID__&callback_url=__CALLBACK_URL__
        header('Content-Type:application/json; charset=UTF-8');
        $get = $this->get();
        $aid = $get['aid'] ?? '';
        $cid = $get['cid'] ?? '';
        $imei = $get['imei'] ?? '';
        $oaid = $get['oaid'] ?? '';
        $androidid = $get['androidid'] ?? '';
        $idfa = $get['idfa'] ?? '';
        $os = $get['os'] ?? '';
        $callback_url = $get['callback_url'] ?? '';
        if (!empty($androidid)) {
            $toutiao = ChannelCallbackModel::find()->where(['androidid' => $androidid])->andWhere(['type' => 'toutiao'])->one();
            if (!$toutiao) {
                $model = new ChannelCallbackModel();
                $attr = [
                    'imei' => $imei,
                    'imeimd5' => '',
                    'oaid' => $oaid,
                    'androidid' => $androidid,
                    'androididmd5' => $idfa,
                    'type' => 'toutiao',
                    'ad_id' => $aid,
                    'ad_name' => $os,
                    'callback_url' => $callback_url,
                    'create_time' => date('Y-m-d H:i:s')
                ];
                $model->setAttributes($attr);
                $model->save(false);
            } else {
                $toutiao->imei = $imei;
                $toutiao->oaid = $oaid;
                $toutiao->androidid = $androidid;
                $toutiao->androididmd5 = $idfa;
                $toutiao->ad_id = $aid;
                $toutiao->ad_name = $os;
                $toutiao->callback_url = $callback_url;
                $toutiao->save(false);
            }
        }
        //回调url
        //$url = $callback_url.'&androidid='.$androidid.'&event_type='.$event_type;
        //$file_contents = file_get_contents($url);
    }

    /*
     * 头条付费回调
     * */
    public function actionTouTiaoBuy()
    {
        $os_type = 2;
        $mobile = 15601615640;
        $oaid = '666';
        $imei = '555';
        $idfa = 'sdfsfgsgsgs';
        if ($os_type == 2 && $idfa) {
            $find = ChannelCallbackModel::find()->where(['androididmd5' => $idfa])
                ->andWhere(['type' => 'toutiao'])
                ->one();
            if ($find) {
                ChannelCallbackModel::updateAll(['imeimd5' => $mobile], ['androididmd5' => $idfa]);
                $event_type = 1;
                //回调url
                $url = $find['callback_url'] . '&idfa=' . $find['androididmd5'] . '&event_type=' . $event_type;
                var_dump($url);
                //file_get_contents($url);
            }
        }
        if ($os_type == 1 && $imei && $oaid) {
            $find = ChannelCallbackModel::find()->where(['imei' => $imei])
                ->andWhere(['oaid' => $oaid])
                ->andWhere(['type' => 'toutiao'])
                ->one();
            if ($find) {
                ChannelCallbackModel::updateAll(['imeimd5' => $mobile], ['imei' => $imei, 'oaid' => $oaid]);
                $event_type = 1;
                //回调url
                $url = $find['callback_url'] . '&imei=' . $find['imei'] . '&event_type=' . $event_type;
                var_dump($url);
                //file_get_contents($url);
            }
        }

    }

    /**
     * 微博落地页接口  注册  激活  付费
     * @return  $output
     */
    public function actionWeibo()
    {
        $mobile = $this->post('mobile');
        //点击数据ID，后续转化数据匹配到点击数据后，按照此ID回传数据
        $mark_id = $this->post('mark_id');
        //from=wb微博标识(非必填)
        $from = $this->post('from');
        $xmob = UserXmobModel::find()->where(['mobile' => $mobile])->one();
        if (!$xmob) {
            $userxmob = new UserXmobModel();
            $time = date("Y-m-d H:i:s");
            $userxmob->mobile = (string)$mobile;
            $userxmob->mob_cid = (string)$mark_id;
            $userxmob->plid = '';
            $userxmob->regist = 1;
            $userxmob->active = 0;
            $userxmob->type = 'weibo';
            $userxmob->create_time = $time;
            $userxmob->save();
            //微博回调参数
            $time = time();
            $host = 'my_xijin_api.com';
            $behavior = 1001;
            //回调url
            $url = 'https://api.biz.weibo.com/track/activate?time=' . $time . '&behavior=' . $behavior . '&mark_id=' . $mark_id . '&host=' . $host;
            //微博后台获取token凭证
            $headerArray = array("Authorization:Bearer acb1d926e400e701b45a10e63a80bac8", "Accept: application/json,application/text+gw2.0");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
            $output = curl_exec($ch);
            curl_close($ch);
            $output = json_decode($output, true);
            return $output;
        }

    }

    //测试订单
    public function actionWeixin()
    {
        $Wx = new WxPay('xijing_v3');
        $goods = '小米10-购买';
        $out_trade_no = '20200615102213';
        $price = 1;
        //$res = $Wx->refund($out_trade_no,$price);
        $res = $Wx->wechat_pay($goods, $out_trade_no, $price, 'APP');
        return $res;
    }

    //测试订单
    public function actionCoupon()
    {
        $coupon = CouponModel::find()->where(['status' => 1])->asArray()->all();
        exit();
    }

    public function actionCeshiyunpian()
    {
        $param = [
            'apikey' => '1f4cd62b2ed12a860b40f2e977df4df3',
            'mobile' => '15601615640',
            'tpl_id' => 3818640,
            'tpl_value' => ('#code#') . '=' . urlencode('2323')
        ];
        //$send_res = post("https://sms.yunpian.com/v2/sms/tpl_single_send.json", $param);
        //$str = 'apikey=1f4cd62b2ed12a860b40f2e977df4df3&mobile='.$mobile.'&tpl_id=3818640&tpl_value='.urlencode('#code#').'='.urlencode($smsCode);
        //var_dump($str);
        //$send_res =  Common::yunpianPost('https://sms.yunpian.com/v2/sms/tpl_single_send.json',$param);
        $num = 13588041592;
        $str = 'HTTP/1.1 200 OK
Server: openresty
Date: Tue, 23 Jun 2020 08:33:25 GMT
Content-Type: application/json;charset=utf-8
Transfer-Encoding: chunked
Connection: keep-alive
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Content-Type-Options: nosniff

{"code":0,"msg":"发送成功","count":1,"fee":0.048,"unit":"RMB","mobile":"15601615640","sid":55373309000}';
        preg_match("/(?<={)[^}]+/", $send_res, $newstr);
        $newstr = '{' . $newstr[0] . '}';
        $res = json_decode($newstr, true);
        if ($res['code'] != 0) {
            var_dump($res);
        }
        /*
        $toutiao = ChannelCallbackModel::find()->where(['user_mobile' => '15601615640'])->andWhere(['type' => 'toutiao'])->andWhere(['status' => 1])->one();
        if ($toutiao) {
            $toutiao->status = 2;
            $toutiao->update_time = date('Y-m-d H:i:s');
            $toutiao->save(false);
            $event_type = 2;
            if(!empty($toutiao['imei'])){
                //回调url
                $url = $toutiao['callback_url'] . '&imei=' . $toutiao['imei'] . '&event_type=' . $event_type;
            }else{
                $url = $toutiao['callback_url'] . '&androidid=' . $toutiao['androidid'] . '&event_type=' . $event_type;
            }
            file_get_contents($url);
        }
        */
    }

    //微信支付成功后接收异步通知并回调微信官方
    public function actionYibu()
    {
        $wx = new WxPay();
        $res = $wx->notify();
        //签名验证通过后期处理
        if ($res) {
            //交易支付成功
            $time = date("Y-m-d H:i:s");
            file_put_contents(Yii::$app->getRuntimePath() . "/logs/wx_success.log", $time . ',' . $res['out_trade_no'] . ',' . $res['total_fee'] . ',' . implode(',', $res) . PHP_EOL, FILE_APPEND);
            $data = array(
                'status' => 2,
                'order_detail' => 'out_trade_no:' . $res['out_trade_no'] . 'trade_no:' . $res['transaction_id'] . 'price:' . $res['total_fee'] / 100,
                'update_time' => date('Y-m-d H:i:s', strtotime($res['time_end'])),
            );
            //订单处理
            GoodsOrderModel::updateAll($data, ['order_sign' => $res['out_trade_no']]);
            $info = array(
                'return_code' => 'SUCCESS',
                'return_msg' => 'OK'
            );
            $formData = $wx->arrayToXml($info);
            return $formData;
        } else {
            return false;
        }
    }


}
