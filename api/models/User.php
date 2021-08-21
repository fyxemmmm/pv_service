<?php

namespace api\models;

use common\models\ChannelCallback1Model;
use Yii;
use common\models\UserModel;
use yii\db\Exception;
use yii\web\IdentityInterface;
use common\models\Common;
use common\models\UserProfileBrowseModel;
use common\models\HuanXin;
use common\models\FocusModel;
use common\models\ArticleModel;
use common\models\ChannelCallbackModel;

class User extends UserModel implements IdentityInterface
{
    const STATUS = 1;

    public function getAuthKey()
    {
    }

    public function getId()
    {
    }

    public function validateAuthKey($authKey)
    {
    }

    public function fields()
    {
        $fields = parent::fields();
        $allowFields = ['id', 'gender', 'nick_name', 'avatar_image', 'register_time', 'last_login_time', 'wechat_token', 'qq_token', 'access_token', 'register_ip', 'mobile', 'huanxin_uuid', 'huanxin_type', 'huanxin_created', 'huanxin_modified', 'huanxin_username', 'huanxin_password', 'huanxin_activated', 'huanxin_nickname'];

        if (Yii::$app->id !== 'app-service') {
            if ($this->id != Yii::$app->params['__web']['user_id']) {
                unset($fields['access_token']);
                unset($fields['wechat_token']);
                unset($fields['qq_token']);
                unset($fields['register_ip']);
                unset($fields['register_time']);
                unset($fields['huanxin_type']);
                unset($fields['huanxin_created']);
                unset($fields['huanxin_modified']);
                unset($fields['huanxin_password']);
                unset($fields['huanxin_activated']);

            }
        }

        if (in_array('register_ip', array_keys($fields))) {
            $fields['register_ip'] = function () {
                return long2ip($this->register_ip);
            };
        }
        foreach ($fields as $k => $v) {
            if (!in_array($k, $allowFields)) unset($fields[$k]);
        }

        return $fields;
    }

    public function extraFields()
    {
        $fields = [];
        $fields['today_browse'] = function () {
            return (int)UserProfileBrowseModel::find()
                ->where(['user_id' => $this->id])
                ->andWhere(['>=', 'create_time', Common::generateDatetime('Y-m-d')])
                ->count();
        };
        $fields['total_browse'] = function () {
            return (int)UserProfileBrowseModel::find()
                ->where(['user_id' => $this->id])
                ->count();
        };
        $fields['focus'] = function () {
            return (int)FocusModel::find()
                ->where(['user_id' => $this->id])
                ->count();
        };
        $fields['article_count'] = function () {
            return (int)ArticleModel::find()
                ->where(['creater' => $this->id])
                ->count();
        };
        return $fields;
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
     * getAccessToken function
     *
     * @param [type] $mobile
     * @return void
     */
    public function getAccessToken(int $mobile, $channel_id = null, $active = 0)
    {
        $user = static::findOne(['mobile' => $mobile]);
        if (!$user) {
            $res = $this->createUser($mobile, $channel_id, $active = 0);
            if ($res['status'] == 1) return Common::messageReturn(1, $res['info']['access_token']);
            return Common::customzieError($res['status'], $res['message'], $res['info']);
        }
        if ($user->status == 0) return Common::messageReturn(0, '用户被注销');
        $datetime = Common::generateDatetime();
        if (!$user->active) {
            $user->active = 1;
            $user->active_time = $datetime;
        }
        $ip = ip2long(Common::getClientIp());
        $user->last_login_ip = $ip;
        $user->last_login_time = $datetime;
        $user->save();
//        $this->modifyActiveStatus($mobile);
        return Common::messageReturn(1, $user['access_token']);
    }


    /*
     * 修改随手花、犀金贷超的状态
     * */
    public function modifyActiveStatus($mobile)
    {
        try {
            /*
             * 随手花
             * */
            $db_sshua = new yii\db\Connection([
                'dsn' => 'mysql:host=rm-uf60e863g9qs2kudv3o.mysql.rds.aliyuncs.com;dbname=sshua',
                'username' => 'sshua_developer',
                'password' => '!@#Abc123',
                'charset' => 'utf8',
            ]);

            /*
             * 犀金贷超
             * */
            $db_xijin_loan = new yii\db\Connection([
                'dsn' => 'mysql:host=rm-uf60e863g9qs2kudv3o.mysql.rds.aliyuncs.com;dbname=xijin_loan',
                'username' => 'xijin_loan_prod',
                'password' => '!@#Abc123',
                'charset' => 'utf8',
            ]);

            $db_list = [$db_sshua, $db_xijin_loan];
            foreach ($db_list as $v) {
                $this->activeStatus($v, $mobile);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }

    }

    public function activeStatus($db, $mobile)
    {
        $data = $db->createCommand("SELECT active,channel_id FROM user WHERE mobile = $mobile")->queryOne();
        if (!$data) return false;
        if ($data['active'] == 0 && $data['channel_id']) {
            $db->createCommand("update user set active = 1 where mobile = $mobile")->execute();
        }
        return true;
    }

    public function registerHuanxinUser($mobile, $accessToken, $nickname)
    {
        $huanxinParams = Yii::$app->params['huanxin'];
        $orgname = $huanxinParams['Orgname'];
        $appkey = $huanxinParams['AppKey'];
        $appName = $huanxinParams['appName'];
        $clientID = $huanxinParams['ClientID'];
        $clientSecret = $huanxinParams['ClientSecret'];
        $huanxin = new HuanXin($orgname, $appkey, $appName, $clientID, $clientSecret);
        return $huanxin->registerUser($mobile, $accessToken, $nickname);
    }

    /**
     * createUser function
     *
     * @param integer $mobile
     * @return string|bool
     */
    public function createUser($mobile, $channel_id = null, $active = 0)
    {
        $accessToken = $this->generateAccessToken();
        $datetime = Common::generateDatetime();
        $ip = ip2long(Common::getClientIp());
        $headers = Yii::$app->request->headers;
        $os = $headers->get('os');
        $platform = $headers->get('platform', '');

        switch ($os) {
            case 'android':
                $os_type = 1;
                break;
            case 'xijin_android':
                $os_type = 1;
                break;
            case 'xijin_ios':
                $os_type = 2;
                $platform = 'apple_store';
                break;
            case 'ios':
                $os_type = 2;
                $platform = 'apple_store';
                break;
            case 'web':
                $os_type = 3;
                $platform = 'web';
                break;
            default:
                $os_type = 0;
                $platform = '';
        }

        $user = new UserModel();
        $res = $this->registerHuanxinUser($mobile, $accessToken, $mobile);
        if (isset($res->error)) { // 已经环信开户
            $huanxinParams = Yii::$app->params['huanxin'];
            $orgname = $huanxinParams['Orgname'];
            $appkey = $huanxinParams['AppKey'];
            $appName = $huanxinParams['appName'];
            $clientID = $huanxinParams['ClientID'];
            $clientSecret = $huanxinParams['ClientSecret'];
            $huanxin = new HuanXin($orgname, $appkey, $appName, $clientID, $clientSecret);
            $res = $huanxin->getUserInfo($mobile);
            $res = json_decode($res);
            $entities = $res->entities[0];
            $user->huanxin_uuid = $entities->uuid;
            $user->huanxin_type = $entities->type;
            $user->huanxin_created = $entities->created;
            $user->huanxin_modified = $entities->modified;
            $user->huanxin_username = $entities->username;
            $user->huanxin_activated = (int)$entities->activated;
            $user->huanxin_nickname = $entities->nickname;
            $user->huanxin_password = $accessToken;
        } else {
            try {
                $entities = $res->entities[0];
            } catch (\Exception $e) {
                $res = json_encode($res);
                file_put_contents('error_huanxin.txt', $res);
            }
            $user->huanxin_uuid = $entities->uuid;
            $user->huanxin_type = $entities->type;
            $user->huanxin_created = $entities->created;
            $user->huanxin_modified = $entities->modified;
            $user->huanxin_username = $entities->username;
            $user->huanxin_activated = (int)$entities->activated;
            $user->huanxin_nickname = $entities->nickname;
            $user->huanxin_password = $accessToken;
        }

        if (!$channel_id) $user->active = 2;
        $user->mobile = (string)$mobile;
        if ($active) $user->active_time = $datetime;
        $user->access_token = $accessToken;
        $user->register_time = $datetime;
//        echo 3;exit;

        $user->register_ip = $ip;
        $user->last_login_ip = $ip;
        $user->last_login_time = $datetime;
        $user->os = $os_type;
        $user->platform = $platform;

        try {
            $user->save();
        } catch (\Exception $e) {
            $user = UserModel::findOne(['mobile' => $mobile]);
        }

        //$this->toutiaoback($os_type, $mobile);
        $this->callback($platform, $os_type);
        if($platform == 'toutiao'){
            $this->toutiaoapk($platform, $mobile);
        }
        if($platform == 'uc'){
            $this->huichuanapk($platform, $mobile);
        }

        return Common::messageReturn(1, '', $user);
    }

    public function toutiaoback($os_type, $mobile)
    {
        $headers = Yii::$app->request->headers;
        $oaid = $headers->get('oaid');
        $imei = $headers->get('imei');
        $idfa = $headers->get('idfa');
        if ($os_type == 2 && $idfa) {
            $find = ChannelCallbackModel::find()->where(['androididmd5' => $idfa])
                ->andWhere(['type' => 'toutiao'])
                ->one();
            if ($find) {
                ChannelCallbackModel::updateAll(['imeimd5' => $mobile], ['androididmd5' => $idfa]);
                $event_type = 1;
                //回调url
                $url = $find['callback_url'] . '&idfa=' . $find['androididmd5'] . '&event_type=' . $event_type;
                file_get_contents($url);
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
                file_get_contents($url);
            }
        }
        return true;
    }

    public function callback($platform, $os_type)
    {
        $headers = Yii::$app->request->headers;
        $oaid = $headers->get('oaid');
        $imei = $headers->get('imei');
        $idfa = $headers->get('idfa');
        $android_id = $headers->get('androidId');
        $openudid = $headers->get('openudid');
        if (2 == $os_type && $idfa) {
            if (false !== strpos($idfa, '000')) {
                $find = ChannelCallbackModel::find()->where(['imeimd5' => $openudid])
                    ->andWhere(['status' => 0])
                    ->one();
            } else {
                $find = ChannelCallbackModel::find()->where(['ad_id' => $idfa])
                    ->andWhere(['status' => 0])
                    ->one();
            }
            if ($find) {
                try {
                    if ('aiqiyi' == $find->type) {
                        $url = urldecode($find->callback_url);
                        $url .= '&event_type=1';

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $output = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($output, true);
                        if (200 == $response['status']) {
                            $find->setAttribute('status', 1);
                            $find->setAttribute('androidid', $response['message']);
                            $find->save();
                        }
                    }
                } catch (\Exception $e) {

                }
            }
        }
        // 对接爱奇艺注册回调
        if ($platform == 'aiqiyi') {
            if (empty($oaid) && empty($imei)) return false;
            $imei = md5($imei);
            $androidid_md5 = md5($android_id);
            $find = ChannelCallbackModel::find()->where([
                'or',
                ['imei' => $imei],
                ['oaid' => $oaid],
                ['androidid' => $android_id],
                ['androidid' => $androidid_md5],
            ])
                ->andWhere(['status' => 0, 'type' => 'aiqiyi'])
                ->one();
            if ($find) {
                try {
                    $url = urldecode($find->callback_url);
                    $url .= '&event_type=1';

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($ch);
                    curl_close($ch);

                    $response = json_decode($output, true);
                    if (200 == $response['status']) {
                        $find->setAttribute('status', 1);
                        $find->setAttribute('androididmd5', $response['message']);
                        $find->save();
                    }
                } catch (\Exception $e) {

                }
            }
        } else if ($platform == 'weibo') {
            if (empty($oaid) && empty($imei)) return false;
            $imei = strtoupper(md5($imei));
            $find = ChannelCallbackModel::find()->where([
                'or',
                ['imeimd5' => $imei],
                ['oaid' => $oaid]
            ])
                ->andWhere(['status' => 0, 'type' => 'weibo'])
                ->one();
            if ($find) {
                try {
                    $url = "http://appmonitor.biz.weibo.com/sdkserver/active?company=xijing&IMP=$find[callback_url]";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($ch);
                    curl_close($ch);

                    $response = json_decode($output, true);
                    if ('OK' == $response['result']) {
                        $find->setAttribute('status', 1);
                        $find->save();
                    }
                } catch (\Exception $e) {

                }
            }
        }

        return true;
    }

    public function toutiaoapk($platform, $mobile)
    {
        $headers = Yii::$app->request->headers;
        $imei = $headers->get('imei');
        $oaid = $headers->get('oaid');
        $android_id = $headers->get('androidId');
        file_put_contents(Yii::$app->getRuntimePath() . "/logs/android_id.log", 'mobile:' . $mobile . 'imeimd5:' . md5($imei) . 'oaid:' . $oaid . ' androidid:' . md5($android_id) . PHP_EOL, FILE_APPEND);
        //头条注册回调
        if ($platform == 'toutiao') {
            if (!empty($imei)) {
                $find = ChannelCallbackModel::find()->where(['imei' => md5($imei)])
                    ->andWhere(['status' => 0, 'type' => 'toutiao'])
                    ->one();
                if ($find) {
                    $find->status = 1;
                    $find->user_mobile = (string)$mobile;
                    $find->update_time = date('Y-m-d H:i:s');
                    $find->save(false);
                    $event_type = 0;
                    //激活回调url
                    $url = $find['callback_url'] . '&imei=' . $find['imei'] . '&event_type=' . $event_type;
                    file_get_contents($url);
                    $event_type = 1;
                    //注册回调url
                    $url = $find['callback_url'] . '&imei=' . $find['imei'] . '&event_type=' . $event_type;
                    file_get_contents($url);
                }
            } else {
                if (!empty($oaid)) {
                    $find = ChannelCallbackModel::find()->where(['oaid' => $oaid])
                        ->andWhere(['status' => 0, 'type' => 'toutiao'])
                        ->one();
                    if ($find) {
                        $find->status = 1;
                        $find->user_mobile = (string)$mobile;
                        $find->update_time = date('Y-m-d H:i:s');
                        $find->save(false);
                        $event_type = 0;
                        //激活回调url
                        $url = $find['callback_url'] . '&oaid=' . $find['oaid'] . '&event_type=' . $event_type;
                        file_get_contents($url);
                        $event_type = 1;
                        //注册回调url
                        $url = $find['callback_url'] . '&oaid=' . $find['oaid'] . '&event_type=' . $event_type;
                        file_get_contents($url);
                    }
                }
                if (!empty($android_id)) {
                    $find = ChannelCallbackModel::find()->where(['androidid' => md5($android_id)])
                        ->andWhere(['status' => 0, 'type' => 'toutiao'])
                        ->one();
                    if ($find) {
                        $find->status = 1;
                        $find->user_mobile = (string)$mobile;
                        $find->update_time = date('Y-m-d H:i:s');
                        $find->save(false);
                        $event_type = 0;
                        //激活回调url
                        $url = $find['callback_url'] . '&androidid=' . $find['androidid'] . '&event_type=' . $event_type;
                        file_get_contents($url);
                        $event_type = 1;
                        //注册回调url
                        $url = $find['callback_url'] . '&androidid=' . $find['androidid'] . '&event_type=' . $event_type;
                        file_get_contents($url);
                    }
                }
            }
        }
        return true;
    }

    public function huichuanapk($platform, $mobile)
    {
        $headers = Yii::$app->request->headers;
        $imei = $headers->get('imei');
        $oaid = $headers->get('oaid');
        $android_id = $headers->get('androidId');
        //头条注册回调
        if ($platform == 'uc') {
            if (!empty($imei)) {
                $find = ChannelCallbackModel::find()->where(['imei' => md5($imei)])
                    ->andWhere(['status' => 0, 'type' => 'uc'])
                    ->one();
                if ($find) {
                    //转化激活为1
                    $find->status = 1;
                    $find->user_mobile = (string)$mobile;
                    $find->update_time = date('Y-m-d H:i:s');
                    $find->save(false);
                    $event_type = 1;
                    //激活回调url
                    $url = urldecode($find['callback_url']) . '&type=' . $event_type;
                    $res = file_get_contents($url);
                    file_put_contents(Yii::$app->getRuntimePath() . "/logs/ali_uc.log", $url . $res . 'end' . PHP_EOL, FILE_APPEND);
                }
            } else {
                if (!empty($oaid)) {
                    $find = ChannelCallbackModel::find()->where(['oaid' => $oaid])
                        ->andWhere(['status' => 0, 'type' => 'uc'])
                        ->one();
                    if ($find) {
                        $find->status = 1;
                        $find->user_mobile = (string)$mobile;
                        $find->update_time = date('Y-m-d H:i:s');
                        $find->save(false);
                        $event_type = 1;
                        //激活回调url
                        $url = $find['callback_url'] . '&type=' . $event_type;
                        $res = file_get_contents($url);
                        file_put_contents(Yii::$app->getRuntimePath() . "/logs/ali_uc.log", $url . $res . 'end' . PHP_EOL, FILE_APPEND);
                    }
                }
                if (!empty($android_id)) {
                    $find = ChannelCallbackModel::find()->where(['androidid' => md5($android_id)])
                        ->andWhere(['status' => 0, 'type' => 'uc'])
                        ->one();
                    if ($find) {
                        $find->status = 1;
                        $find->user_mobile = (string)$mobile;
                        $find->update_time = date('Y-m-d H:i:s');
                        $find->save(false);
                        $event_type = 1;
                        //激活回调url
                        $url = $find['callback_url'] . '&type=' . $event_type;
                        $res = file_get_contents($url);
                        file_put_contents(Yii::$app->getRuntimePath() . "/logs/ali_uc.log", $url . $res . 'end' . PHP_EOL, FILE_APPEND);
                    }
                }
            }
        }
        return true;
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
    public function setPassword($password)
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
