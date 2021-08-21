<?php
namespace common;
use common\models\UserModel;
use common\models\Common;

/*
 * 桥接信息及各种配置
 * */
class Bridge{
    CONST XIJIN_LOAN_API_DEV_URL = 'http://47.103.61.179:8011';
    CONST XIJIN_LOAN_API_PROD_URL = 'https://api.loan.xykj1.com';
    CONST SALT = 'xiyikeji888';  // api对称秘钥

    CONST JMB_SHARE_DEV_URL = 'http://47.103.61.179:1080/jmbshare/?id=';
    CONST JMB_SHARE_PROD_URL = 'https://api.xykj1.com/jmbshare/?id=';

    CONST LB_SHARE_DEV_URL = 'http://47.103.61.179:1080/invate_friend/';  // 个人中心分享URL 带token过去
    CONST LB_SHARE_PROD_URL = 'https://api.xykj1.com/invate_friend/';

    CONST XIJIN_LOAN_WAP_DEV_URL = 'http://47.103.61.179:8077'; //犀金贷超wap
    CONST XIJIN_LOAN_WAP_PROD_URL = 'http://wap.loan.xykj1.com';

    CONST VIP_WAP_DEV_URL = 'http://47.103.61.179:1082';
    CONST VIP_WAP_PROD_URL = 'http://www.xykj1.com/gather';

    CONST SILVER = 0;
    CONST GOLD = 1;
    CONST DIAMOND = 2;

    CONST SILVER_COUNT = 0;  // 默认没有下家就是白银级
    CONST GOLD_COUNT = 10;  // 黄金级别需要10个下家
    CONST DIAMOND_COUNT = 510;  // 钻石级别需要510个下家 (原来110)

    CONST LEVEL = [
        self::SILVER => '普通代理',
        self::GOLD => '黄金级',
        self::DIAMOND => '会员代理'
    ];

    // 用户的绑定关系白名单
    CONST RELA_ID_WHITE_LIST = [
        '22134'
    ];

    CONST RETURN_RATE = 0; // 默认返现
    CONST RETURN_AWARD = 1; // 返固定金额

    CONST AUDIT_NOT_PASS = 0;  // 甲方审核未通过
    CONST AUDITING = 1;  // 甲方审核中
    CONST AUDIT_PASS = 2;  // 甲方审核已通过 或者我们手动切换成已下款状态
    CONST PASS_AUDIT = 1; // 审核状态由审核中变为已下款

    CONST RETURN_TYPE_DEFAULT = 0; // 类型 产品通过后台导表返现
    CONST RETURN_TYPE_REPORT = 1; // 类型 产品通过报备返现

    // 提现相关
    CONST APPLY_DOING = 0; // 正在提现 (申请中)
    CONST APPLY_PASS = 1; // 提现成功 (已经打钱给用户)
    CONST APPLY_REJECT = 2; // 提现失败 会标注理由

    CONST ATTENDANCE_QD = 0; // 签到表 0用户中心签到类型

    CONST INCOME_TYPE_DEFAULT = 0; // income表 类型 0下家已下款的打到此表
    CONST INCOME_TYPE_QD = 1; // income表 类型 1签到
    CONST INCOME_TYPE_FIRST_AWARD = 2; // income表 类型 2下家第一次下款

    CONST CUSTOMER_NAME = '小犀客服';  // 客服名

    public $xijin_loan_url;
    public $jmb_share_url;
    public $lb_share_url;
    public $xijin_loan_wap_url;
    public $vip_wap_url;

    public function __construct()
    {
        if ('dev' == YII_ENV) {
            $this->xijin_loan_url = self::XIJIN_LOAN_API_DEV_URL;
            $this->jmb_share_url = self::JMB_SHARE_DEV_URL; // 加盟宝分享链接
            $this->lb_share_url = self::LB_SHARE_DEV_URL;
            $this->xijin_loan_wap_url = self::XIJIN_LOAN_WAP_DEV_URL;
            $this->vip_wap_url = self::VIP_WAP_DEV_URL;
        }else{
            $this->xijin_loan_url = self::XIJIN_LOAN_API_PROD_URL;
            $this->jmb_share_url = self::JMB_SHARE_PROD_URL; // 加盟宝分享链接
            $this->lb_share_url = self::LB_SHARE_PROD_URL;
            $this->xijin_loan_wap_url = self::XIJIN_LOAN_WAP_PROD_URL;
            $this->vip_wap_url = self::VIP_WAP_PROD_URL;
        }
    }

    public static function returnJson($code = 0, $data = null){
        die(json_encode(['code' => $code, 'data' => $data]));
    }

    /*
     * 获取该用户的贷超等级信息  如果有权益,那么还多需判断
     * */
    public function getUserDcLevel($mobile){
        $level = UserModel::find()->select('user_level')->where(['mobile' => $mobile])->scalar();
        $user_id = UserModel::find()->select('id')->where(['mobile' => $mobile])->scalar();
        if($user_id){
            $is_vip = Common::checkVip($user_id);
            if($is_vip){
                if($level == Bridge::SILVER){
                    return Bridge::GOLD; // 如果是白银级,但是是犀金权益会员,那么默认给他黄金级别
                }
            }
        }
        return $level;
    }

    /*
     * 生成绑定用户关系的标识
     * */
    public static function genSign($user_id){
        $res = self::num2alpha($user_id);
        if(strlen($res) < 6){
            $res =  str_pad($res,6,"0",STR_PAD_LEFT);
        }
        return $res;
    }

    /*
     * 解析(上家)用户id
     * */
    public static function decodeSign($sign){
        $match = preg_match('/^(0*)([a-z]+)$/',$sign, $matches);
        if(empty($match)) return false;
        $str = $matches[2];
        $user_id = self::alpha2num($str);
        if(!$user_id) return false;
        return $user_id;  // 上家用户id
    }


    /*
     * 生成随机字符串
     * */
    public static function createRandomStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public static function num2alpha($intNum, $isLower=true)
    {
        $num26 = base_convert($intNum, 10, 26);
        $addcode = $isLower ? 49 : 17;
        $result = '';
        for ($i=0; $i<strlen($num26); $i++) {
            $code = ord($num26{$i});
            if ($code < 58) {
                $result .= chr($code+$addcode);
            } else {
                $result .= chr($code+$addcode-39);
            }
        }
        return $result;
    }

    public static function alpha2num($strAlpha)
    {
        if (ord($strAlpha{0}) > 90) {
            $startCode = 97;
            $reduceCode = 10;
        } else {
            $startCode = 65;
            $reduceCode = -22;
        }
        $num26 = '';
        for ($i=0; $i<strlen($strAlpha); $i++) {
            $code = ord($strAlpha{$i});
            if ($code < $startCode+10) {
                $num26 .= $code-$startCode;
            } else {
                $num26 .= chr($code-$reduceCode);
            }
        }
        return (int)base_convert($num26, 26, 10);
    }

    /*
     * 获取某个犀金产品的信息
     * */
    public function fetchProductInfo($product_id){
        $res =  Common::requestPost($this->xijin_loan_url . "/bridge/get-product-info", ['key' => self::SALT, 'id' => $product_id]);
        $res = json_decode($res, true);
        if(empty($res)) return ['message' => 'remote addr err'];
        if($res['code'] != 1) return ['message' => $res['message']];
        return $res['info']; // 成功返回信息
    }

    /*
     * 如果下家首次下款,那么上家会获得对应承诺的返佣
     * 无限,很多下家首次下款,那么该上家都会获得该笔奖励
     * */
    public function fetchFirstAward($product_id){
        $res =  Common::requestPost($this->xijin_loan_url . "/bridge/get-first-award", ['key' => self::SALT, 'id' => $product_id]);
        $res = json_decode($res, true);
        if(empty($res)) return ['message' => 'remote addr err'];
        if($res['code'] != 1) return ['message' => $res['message']];

        return $res['info']['award']; // 成功返回信息
    }

    /*
     * 获取token
     * */
    public function fetchAccessToken($mobile){
        $res =  Common::requestPost($this->xijin_loan_url . "/bridge/get-token", ['key' => self::SALT, 'mobile' => $mobile]);
        $res = json_decode($res, true);
        return $res['info']['access_token'];
    }


}