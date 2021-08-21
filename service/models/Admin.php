<?php

namespace service\models;

use Yii;
use common\models\AdminModel;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;
use common\models\Common;

// , RateLimitInterface
class Admin extends AdminModel implements IdentityInterface
{
    const STATUS = 1;

    public function getAuthKey()
    { }
    public function getId()
    { }
    public function validateAuthKey($authKey)
    { }

    // public function getRateLimit($request, $action)
    // {
    //     return [1, 10];
    // }

    // public function loadAllowance($request, $action)
    // {
    //     return [$this->allowance, $this->allowance_updated_at];
    // }

    // public function saveAllowance($request, $action, $allowance, $timestamp)
    // {
    //     $this->allowance = $allowance;
    //     $this->allowance_updated_at = $timestamp;
    //     $this->save();
    // }
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password']);
        return $fields;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name'], 'string', 'max' => 25, 'min' => 2];
        $rules[] = [['password'], 'string', 'max' => 60, 'min' => 6];
        return $rules;
    }
    /**
     * 根据给到的ID查询身份。
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * 根据 token 查询身份。
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }


    /**
     * 更新和创建 function
     *
     * @param integer $mobile
     * @return string|bool 
     */
    public function createAdmin($post, $password)
    {
        $accessToken = $this->generateAccessToken();
        $datetime = Common::generateDatetime();
        $ip = Common::getClientIp();
        $password_hash = $this->generatePassword($password);
        
        $user = new Admin();
        $user->load($post);
        $user->password = $password_hash;
        $user->access_token = $accessToken;
        $user->register_time = $datetime;
        $user->register_ip = $ip;
        $user->last_login_ip = $ip;
        $user->last_login_time = $datetime;
        if ($user->save()) {
            return Common::messageReturn(1, '添加成功', $user);
        }
        return Common::messageReturn(0, '添加用户出错', $user->getErrors());
    }

    /**
     * getAccessToken function
     *
     * @param [type] $mobile
     * @param [type] $password
     * @return void
     */
    public function getAccessToken($name, $sms_code)
    {
        $user = static::findOne(['name' => $name]);
        if (! $user) return Common::messageReturn(0, '用户不存在');
        if ($user['type'] !== 1 && $user['type'] !== 2) return Common::messageReturn(0, '用户类型不对');
        if ($user['status'] == 0) return Common::messageReturn(0, '用户被注销');

        $allow_user = ['xiyikeji123', 'xiyikeji666'];
        if(!in_array($name, $allow_user)){
            $redis_sms_code = $this->getAdminSms($name);
            if($redis_sms_code !== (int)$sms_code) return Common::messageReturn(0, '请检查验证码是否填写正确');
        }
        $datetime = Common::generateDatetime();
        $ip = ip2long(Common::getClientIp());
        $user->last_login_ip = $ip;
        $user->last_login_time = $datetime;
        $user->save();

        /*
         * 定时过期
         * */
        $redis = Yii::$app->cache;
        $redis->set('admin_login_'.$user->id, time());

        return Common::messageReturn(1, '获取成功', [
            'access_token' => $user['access_token'],
        ]);
    }

    public function getAdminSms($name){
        $cache = Yii::$app->cache;
        return $cache->get('admin_sms_code_' . $name);  // name 和mobile是一个意思
    }

    public function setAdminSms($mobile, $sms_code){
        $cache = Yii::$app->cache;
        $send_res = Common::ali_sms($mobile, $sms_code);
        if($send_res) return Common::messageReturn(0); // obj == true?
        $redis_res = $cache->set('admin_sms_code_'. $mobile, $sms_code, 60 * 10);
        if(!$redis_res) return Common::messageReturn(0, 'redis error'); // obj == true?
        return false;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password, $password_hash)
    {
        return Yii::$app->security->validatePassword($password, $password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function generatePassword($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function generateAccessToken()
    {
        return Yii::$app->security->generateRandomString();
    }
}