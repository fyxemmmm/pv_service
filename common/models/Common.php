<?php
namespace common\models;

use api\models\User;
use common\Bridge;
use common\VarTmp;
use Yii;
use Leslie\Sms\Sms;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use PHPQRCode\QRcode;
use common\models\ServiceApiLogModel;
use Mrgoon\AliSms\AliSms;
use common\models\InterestPayserialModel;
use common\models\UserIdCardNoModel;

class Common
{

    /**
     * 自定义403错误
     *
     * @param string $message
     * @param integer $code
     * @param integer $status
     * @return void
     */
    public static function customzieError($message = "", $code = 0, $status = 403){
        Yii::$app->response->statusCode = $status;
        throw new ForbiddenHttpException($message, $code);
    }

    /**
     * 自定义404错误
     *
     * @param string $message
     * @param integer $code
     * @param integer $status
     * @return void
     */
    public static function customzieErrorNotFound($message = "", $code = 0, $status = 404){
        Yii::$app->response->statusCode = $status;
        throw new NotFoundHttpException($message, $code);
    }
    /**
     * 服务端响应
     *
     * @param integer $code
     * @param string $message
     * @param string $info
     * @param integer $statusCode
     * @return object $response.
     */
    public static function response($code = 1, $message = "", $info = "", $statusCode = 200){
        $response = Yii::$app->response;
        $response->statusCode = $statusCode;
        $response->format = \yii\web\Response::FORMAT_JSON;
       
        $response->data = [
            'code' => $code,
            'message' => $message,
            'info' => $info,
        ];
        return $response;
    }

    /**
     * 获取或者设置短信
     *
     * @param integer $mobile
     * @param [type] $smsCode
     * @return void
     */
//    public static function toggleSmsCode(int $mobile, int $smsCode = 0)
//    {
//        $cache = Yii::$app->cache;
//        if ($smsCode === 0) return $cache->get('sms_code_' . $mobile);
//        $send_res =  self::ali_sms($mobile, $smsCode);
//        if ($send_res) return Common::messageReturn(0, '短信发送失败', $send_res);
//        $r = $cache->set('sms_code_' . $mobile, $smsCode, 60 * 10);
//        if (!$r) return Common::messageReturn(0, 'redis error', $r);
//        return false; // success
//    }
//


    public static function toggleSmsCode(int $mobile, int $smsCode = 0)
    {
        $cache = Yii::$app->cache;
        if ($smsCode === 0) return $cache->get('sms_code_' . $mobile);
        $send_res =  self::ali_sms($mobile, $smsCode);
        if ($send_res) return Common::messageReturn(0, '短信发送失败', $send_res);
        $r = $cache->set('sms_code_' . $mobile, $smsCode, 60 * 10);
        if (!$r) return Common::messageReturn(0, 'redis error', $r);
        return false; // success
    }

    public static function yunpianSmsCode(int $mobile, int $smsCode = 0)
    {
        $cache = Yii::$app->cache;
        if ($smsCode === 0) return $cache->get('sms_code_' . $mobile);
        $param = [
            'apikey' => '1f4cd62b2ed12a860b40f2e977df4df3',
            'mobile' => (string)$mobile,
            'tpl_id' => 3818640,
            'tpl_value' => ('#code#').'='.urlencode($smsCode)
        ];
        //$send_res = post("https://sms.yunpian.com/v2/sms/tpl_single_send.json", $param);
        //$str = 'apikey=1f4cd62b2ed12a860b40f2e977df4df3&mobile='.$mobile.'&tpl_id=3818640&tpl_value='.urlencode('#code#').'='.urlencode($smsCode);
        //var_dump($str);
        Common::yunpianPost('https://sms.yunpian.com/v2/sms/tpl_single_send.json',$param);

        $r = $cache->set('sms_code_' . $mobile, $smsCode, 60 * 10);
        if (!$r) return Common::messageReturn(0, 'redis error', $r);
        return false; // success
    }

    /**
     * 发起post请求
     *
     * @param string $url
     * @param array $param
     * @return object $data
     */
    public static function yunpianPost($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $headers=array();
        $headers[] = "Accept:application/json;charset=utf-8";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $postUrl = $url;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, $headers);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        //连接等待时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 5);
        // 最大执行时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);//运行curl
        //ar_dump(curl_error($ch));
        curl_close($ch);
        return $data;
    }

    /**
     * ali_sms function
     *
     * @param [type] $mobile
     * @param [type] $smsCode
     * @return void
     */
    public static function ali_sms($mobile, $smsCode)
    {
        $sms= new AliSms();
        $sms_local_config = Yii::$app->params['aliyun']['dysms'];
        $config = [
            'access_key' => $sms_local_config['AccessKeyID'],
            'access_secret' => $sms_local_config['AccessKeySecret'],
            'sign_name' => $sms_local_config['sign_name'][0],
        ];
        $template = $sms_local_config['template'][0];
        $res = $sms->sendSms($mobile, $template, ['code'=> $smsCode], $config);
        if ($res->Code == 'OK') return false;
        return $res->Message;
    }


    /*
     * 发送短信 激活用于激活
     * */
    public static function send_active_msg($mobile, $type)
    {
        $sms = new AliSms();
        if($type == 'xijin'){
            $sms_local_config = Yii::$app->params['aliyun']['xijin_active'];
        }elseif ($type == 'sshua'){
            $sms_local_config = Yii::$app->params['aliyun']['sshua_active'];
        }elseif ($type == 'cqqb'){
            $sms_local_config = Yii::$app->params['aliyun']['cqqb_active'];
        }else{
            return false;
        }
        $config = [
            'access_key' => $sms_local_config['AccessKeyID'],
            'access_secret' => $sms_local_config['AccessKeySecret'],
            'sign_name' => $sms_local_config['sign_name'][0],
        ];
        $template = $sms_local_config['template'][0];
        $res = $sms->sendSms($mobile, $template, ['name'=>$mobile],$config);
        if ($res->Code == 'OK') return false;
        return $res->Message;
    }


    /**
     * 云片网送短信接口
     *
     * @param integer $mobile
     * @param integer $smsCode
     * @return mixed(bool | object)
     */
    public static function sendSms(int $mobile, int $smsCode)
    {
        $yunpianSms = Yii::$app->params['yunpian_sms'];
        $apikey = $yunpianSms['apikey'];
        $v1_url = $yunpianSms['v1'];
        $sshua_app_tp_id = $yunpianSms['template_id']['sshua_app_tp_id'];
        $tp_val = urlencode("#code#=" . $smsCode);
        $postString = "apikey=$apikey&mobile=$mobile&tpl_id=$sshua_app_tp_id&tpl_value=$tp_val";
        $r = Common::requestPost($v1_url, $postString);
        $obj = json_decode($r);
        if ($obj->code == 0) return false; // success
        return $obj;
    }

    /**
     * 螺丝帽短信
     *
     * @param [type] $mobile
     * @param [type] $content
     * @return void
     */
    public static function LsmSms($mobile, $content)
    {
        $sms = new Sms(['api_key' => Yii::$app->params['luosimao']['api_key'], 'use_ssl' => false]);
        $res = $sms->send($mobile, $content);
        return $res;
    }

    /**
     * 创蓝短信
     *
     * @param [type] $mobile
     * @param [type] $code
     * @return void
     */
    public static function chuanglanSms($mobile, $code) 
    {
        $chuanglan_sms = Yii::$app->params['chuanglan_sms'];
        $sms = new ChuanglanSms($chuanglan_sms['account'], $chuanglan_sms['password']);
        $content = self::factory()($code);
        $res =  $sms->sendSMS($mobile, $content);
        if (0 == $res->code) return false;
        return $res;
    } 

    public static function  factory() {
        $template = Yii::$app->params['chuanglan_sms']['template'];
        return function($code) use ($template) {
            return eval('return "' . $template . '";');
        };
    }
    /**
     * 验证手机号
     *
     * @param integer $mobile
     * @return bool
     */
    public static function verifiyMobile($mobile)
    {
        if (preg_match("/^1[23456789]\d{9}$/", $mobile)) return true;
        return false;   
    }

    /**
     * 方法向上层返回信息
     *
     * @param integer $static
     * @param string $message
     * @param string $info
     * @return array
     */
    public static function messageReturn($status = 1, $message = "", $info = "") {
        return [
            'status' => $status,
            'message' => $message,
            'info' => $info
        ];
    }

    /**
     * 发起post请求
     *
     * @param string $url
     * @param string $param
     * @return object $data
     */
    public static function requestPost($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        //连接等待时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 3);
        // 最大执行时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }

    /**
     * 生成时间格式
     *
     * @param string $format
     * @param integer $timestamp
     * @return void
     */
    public static function generateDatetime($format = "Y-m-d H:i:s", int $timestamp = 0){
        if ($timestamp == 0) return date($format, time());
        return date($format, $timestamp);
    }
    

    public static function getClientIp()
    {
       return Yii::$app->request->userIP; 
    }

    public static function dataProvider($query, $pagination = 10, $sort = '')
    {
        if ($sort) $sort_arr = static::generateSort($sort);
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ]
        ]);
    }

    public static function generateSort($sort)
    {
        $arr = explode(',', $sort);
    }

    public static function qrcode($conent, $file, $level = 'L', $size = 4)
    {
        $res = QRcode::png($conent, $file, $level, $size, 2);
        if ($res) return false;
        return true;
    }

    public static function apiLog($controller_id, $action_id, $type = 0)
    {
        $api = new ServiceApiLogModel();
        $api->app_name = Yii::$app->id;
        $api->controller = $controller_id;
        $api->action = $action_id;
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        $api->status_code = $response->statusCode;
        $api->request_type = $request->method;
        $api->input = $request->bodyParams ? $request->bodyParams : $request->queryParams;
        if(VarTmp::$admin_id){
            $api->input = array_merge(['_admin_id' => VarTmp::$admin_id], $api->input);
        }
        $api->type =  $type;
        $api->ip = ip2long(static::getClientIp());
        $api->create_time = static::generateDatetime();
        $api->response = $response->data;
        return $api->save();
    }

    /**
     * 阿里云上传方法
     *
     * @param string $type 属于什么的图片,会分别上传到什么文件路径中, 例如  'article' => 'article/images/'. 详情见common/config/params.php
     * @param string $file 文件在本机中的绝对路径
     * @param string $file_name 保存到oss时的文件名
     * @param string $file_oss_path 如不设置$type, 可通过此参数设置,文件要存放的路径, 如果$type,$file_oss_path都未设置则保存到 others中
     * @return array [status, oss_path, $detailMesage]
     */
    public static function uploadToAliyun_oss($type = '', $file, $file_name, $file_oss_path = '', $protocol = true)
    {
        if (! $file) return Common::messageReturn(0, 'file must be set');
        $params = Yii::$app->params;
        if ($type) $oss_path = $params['oss_path'][$type]; 
        if (! $type) $oss_path = $file_oss_path ? $file_oss_path . '/' : 'others/';
        $file_name = $oss_path . $file_name;

        $aliyun_oss = new AliyunOss();
        $result = $aliyun_oss->uploadFile($file_name, $file);
        if (! $result) return Common::messageReturn(0, '阿里云上传错误');
        if ($protocol) $result['info']['url'] = 'https' . substr($result['info']['url'], 4);
        return Common::messageReturn(1, $result['info']['url'], $result['info']);
    }


    /*
     * 检查是否是犀金的vip
     * */
    public static function checkVip($user_id){
        $date = date('Y-m-d H:i:s');
        $is_vip = InterestPayserialModel::find()->where(['user_id' => $user_id])->andWhere(['>','finish_time', $date])->one();
        return $is_vip ? true : false;
    }

    /*
     * 生成订单号
     * */
    public static function genOrderSign(){
        // 多删了2个字符
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
    }

    /*
     * 生成一点钱
     * */
    public static function genLittleMoney(){
        $w = random_int(0,4); // 角
        $f = random_int(1,9); // 分
        return (string)"0.".$w.$f;
    }


    /*
     * 检查是否已经实名认证
     * */
    public static function checkReal($user_id){
        $is_exist = UserIdCardNoModel::find()->where(['user_id' => $user_id])->one();
        return $is_exist ? true : false;
    }


    /*
     * 生成图形二维码
     * */
    public static function genCodePic($user_id){
        //1.创建黑色画布
        $image = imagecreatetruecolor(100, 30);

        //2.为画布定义(背景)颜色
        $bgcolor = imagecolorallocate($image, 254, 251, 240); // 乳白色

        //3.填充颜色
        imagefill($image, 0, 0, $bgcolor);

        // 4.设置验证码内容

        //4.1 定义验证码的内容
        $content = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        //4.1 创建一个变量存储产生的验证码数据，便于用户提交核对
        $captcha = "";
        for ($i = 0; $i < 4; $i++) {
            // 字体大小
            $fontsize = 20;
            // 字体颜色
            $fontcolor = imagecolorallocate($image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
            // 设置字体内容
            $fontcontent = substr($content, mt_rand(0, strlen($content)), 1);
            $captcha .= $fontcontent;
            // 显示的坐标
            $x = ($i * 100 / 4) + mt_rand(5, 10);
            $y = mt_rand(5, 10);
            // 填充内容到画布中
            imagestring($image, $fontsize, $x, $y, $fontcontent, $fontcolor);
        }


//        //4.3 设置背景干扰元素
//        for ($$i = 0; $i < 200; $i++) {
//            $pointcolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
//            imagesetpixel($image, mt_rand(1, 99), mt_rand(1, 29), $pointcolor);
//        }

        //4.4 设置干扰线
//        for ($i = 0; $i < 3; $i++) {
//            $linecolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
//            imageline($image, mt_rand(1, 99), mt_rand(1, 29), mt_rand(1, 99), mt_rand(1, 29), $linecolor);
//        }

        //5.向浏览器输出图片头信息
        // header('content-type:image/png');

        $file_name = 'codePic' . $user_id . '.png'; // 生成二维码

        //6.输出图片到浏览器 到文件
        imagepng($image, $file_name);

        $info = self::uploadToAliyun_oss('',$file_name,'user/codePic/'.$file_name);

        //7.销毁图片
        imagedestroy($image);

        $cache = Yii::$app->cache;
        $cache->set('codePic' . $user_id, strtoupper($captcha),120 * 10); // 20 分钟

        try{
            unlink($file_name);
        }catch (\Exception $e){
            // pass
        }

        return $info['message'];
    }



    // 下载文件
    public static function downfile($file)
    {
        if(!$file){
            exit('未找到要下载的文件');
        }

        if (file_exists($file)) {

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }

    /*
     * 购买商品是否可以打折
     * */
    public static function checkShopDiscount($user_id, $goods_id = null){
        if(empty($user_id)) return false;
        if($goods_id !== null){
            $has_shared = GoodsUserShareModel::find()->where(['user_id' => $user_id, 'goods_id'=>$goods_id])->one();
        }else{
            $has_shared = false;
        }
        $level = (int)User::find()->select(['user_level'])->where(['id' => $user_id])->scalar();
        $is_black_vip = InterestPayserialModel::find()->where(['user_id' => $user_id, 'card_id' => 3])->andWhere(['>','finish_time',date('Y-m-d H:i:s')])->one() ? true : false;
        $is_vip = JushengVipModel::find()->where(['user_id' => $user_id])->andWhere(['>=', 'expire_time', date('Y-m-d H:i:s')])->one();

        $app_name = Yii::$app->request->headers['name'];
        $is_xiaoying_vip = false;
        if ('xijing' != $app_name) {
            $is_xiaoying_vip = XiaoyingVipOrderModel::find()->where(['user_id' => $user_id, 'trade_status' => 1])->one();
            $is_vip = false;
        }

        if(!$has_shared && $level !== Bridge::DIAMOND && !$is_black_vip && !$is_vip && !$is_xiaoying_vip) return false;
        return true;
    }


    /*
       * 获取物流的信息
       * com 快递公司编码
       * num 快递单号
       * */
    public static function getLogisticsInfo($com, $num){
        //参数设置
        $post_data = array();
        $post_data["customer"] = '659DAC40CBE0AB2338BB443C5FD16509';
        $key= 'PDJkpiQp1305' ;
        $template =  '{"com":"#","num":"@"}';
        $template = str_replace('#',$com, $template);
        $template = str_replace('@',$num, $template);
        $post_data["param"] = $template;
        $url='http://poll.kuaidi100.com/poll/query.do';
        $post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($post_data["sign"]);
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
        }
        $post_data=substr($o,0,-1);
        $result = self::requestPost($url, $post_data);
        $data = str_replace("\"",'"',$result );
        $data = json_decode($data,true);
        return $data;
    }


    /*
     *  分享 invite_friend 的url
     * */
    public static function getInviteUrl($user_id){
        $bridge = new Bridge();
        $invite_sign = Bridge::genSign($user_id); // 邀请码
        return  $bridge->lb_share_url . '?sign='.$invite_sign . '&callback=' .$bridge->xijin_loan_wap_url . '/loan/2.html?sign='.$invite_sign;
    }


    /*
     * 按折扣计算每个规格的价格
     * */
    public static function calcPrice($purchasing_cost, $goods_info){
        $good_tax = $goods_info['good_tax'];  // 商品的税费用
        $profitable_rate = $goods_info['profitable_rate']; // 我们要盈利的
        $logistics_fee =  $goods_info['logistics_fee']; // 物流费
        $discount = $goods_info['discount'];
        $after_discount_cost = $purchasing_cost + ($purchasing_cost + $logistics_fee) * ($good_tax + (1+$good_tax) * $profitable_rate);
        $original_cost = $after_discount_cost / ($discount / 10);
        return [$original_cost, $after_discount_cost];
    }

    /*
     * 巨省的劵生成
     * */
    public static function genCoupon($user_id){
        if(empty($user_id)) return;
        // 前3个月发送10元话费劵 后12个月发12个5元的劵  话费劵
        $create_time = date('Y-m-d H:i:s');
        for($i = 0;$i < 15; $i ++){
            if($i == 0){
                $start_time = date('Y-m-d 00:00:00',strtotime(date('Y-m-d 00:00:00'))+ $i*86400*30) ;
            }else{
                $start_time = date('Y-m-d 00:00:00',strtotime(date('Y-m-d 00:00:00'))+ $i*86400*30 + 86400) ;
            }
            $end_time = date('Y-m-d 23:59:59',strtotime(date('Y-m-d 00:00:00')) + $i*86400*30 + 86400*30);
            if($i < 3){ // 10元话费劵  3张
                $supplier_coupon_code = 'AEAAA39EFCB84BD2D201FB1E1488C18D';
                $type = 2;
            }else{ // 5元话费劵
                $supplier_coupon_code = '347BE7835B0E5423C4760FEA7A8A7807';
                $type = 3;
            }

            $coupon_code = strtoupper(session_create_id().uniqid()); // 劵唯一id
            $coupon_model = new JushengCouponModel();
            $attributes = [
                'coupon_code' => $coupon_code,
                'user_id' => (string)$user_id,
                'supplier_coupon_code' => $supplier_coupon_code,
                'coupon_status' => (string)0, // 未使用
                'type' => $type,
                'create_time' => $create_time,
                'start_time' => $start_time,
                'end_time' => $end_time
            ];
            $coupon_model->setAttributes($attributes);
            $coupon_model->save();
        }

        // 2周视频劵
        for($i = 0;$i < 2; $i ++) {
            if($i == 0){
                $start_time = date('Y-m-d 00:00:00',strtotime(date('Y-m-d 00:00:00'))+ $i*86400*7) ;
            }else{
                $start_time = date('Y-m-d 00:00:00',strtotime(date('Y-m-d 00:00:00'))+ $i*86400*7 + 86400) ;
            }
            $end_time = date('Y-m-d 23:59:59',strtotime(date('Y-m-d 00:00:00')) + $i*86400*7 + 86400*7);
            $supplier_coupon_code = 'CC9E205C150269A92116537966167BC2'; // 视频劵
            $type = 1; // 视频劵
            $coupon_code = strtoupper(session_create_id().uniqid()); // 劵唯一id
            $coupon_model = new JushengCouponModel();
            $attributes = [
                'coupon_code' => $coupon_code,
                'user_id' => (string)$user_id,
                'supplier_coupon_code' => $supplier_coupon_code,
                'coupon_status' => (string)0, // 未使用
                'type' => $type,
                'create_time' => $create_time,
                'start_time' => $start_time,
                'end_time' => $end_time
            ];
            $coupon_model->setAttributes($attributes);
            $coupon_model->save();
        }

        //商品优惠券生成
        $coupon = CouponModel::find()
            ->where(['status'=>1])
            ->orderBy('create_time desc')
            ->asArray()
            ->all();
        foreach ($coupon as $key=>$val){
            $coupon_user = CouponUserModel::find()
                ->where(['coupon_code' => $val['coupon_code'], 'user_id' => $user_id])->one();
            if(empty($coupon_user)){
                $coupon_model = new CouponUserModel();
                $start_time = date("Y-m-d 00:00:00");
                $end_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00:00')) + 86400 * (int)$val['overdue'] - 1);
                $attributes = [
                    'coupon_code' => $val['coupon_code'],
                    'user_id' => $user_id,
                    'coupon_status' => 0, // 未使用
                    'start_time' => $start_time,
                    'end_time' => $end_time
                ];
                $coupon_model->setAttributes($attributes);
                $coupon_model->save(false);
            }
        }

        return;
    }

    public static function getAppSwitchKey($key = '')
    {
        $AppSwitchModel = AppSwitchModel::findOne($key);
        if ($AppSwitchModel) {
            return $AppSwitchModel->value;
        } else {
            return '';
        }
    }

    public static function quxiao($order_id = ''){
        $model = GoodsOrderModel::find()->where(['id' => $order_id,'status' => 1])->one();
        if ($model){
            if(!empty($model->coupon_code)){
                $coupon = CouponUserModel::find()->where(['coupon_code' => $model->coupon_code,'user_id'=>$model->user_id, 'coupon_status' => 3])->one();
                if($coupon){
                    $data['coupon_status'] = 0;
                    $coupon->setAttributes($data);
                    $coupon->save(false);
                }
            }
        }
    }

    public static function setAppSwitchKey($key, $value)
    {
        $AppSwitchModel = AppSwitchModel::findOne($key);
        if ($AppSwitchModel) {
            $AppSwitchModel->setAttribute('value', $value);
        } else {
            $AppSwitchModel = new AppSwitchModel();
            $AppSwitchModel->setAttributes([
                'key' => $key,
                'value' => $value
            ]);
        }
        return $AppSwitchModel->save();
    }

}
