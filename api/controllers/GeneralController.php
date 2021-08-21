<?php

namespace api\controllers;

use common\Config;
use Yii;
use common\models\BannerModel;
use common\models\AppVersionModel;
use common\models\AppVersionModelSearch;
use common\models\Common;
use common\models\Upload;
use common\models\FeedbackModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\AppSettingModel;
use common\models\HuanXin;
use common\models\UserModel;
use AliCloud\Core\Profile\DefaultProfile;
use AliCloud\Core\DefaultAcsClient;
use AliCloud\Core\Exception\ServerException;
use AliCloud\Core\Exception\ClientException;
use AliCloud\STS\AssumeRoleRequest;
use common\Helper;


class GeneralController extends CommonController
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
            'except' => ['get-banner', 'app-check-version', 'feedback', 'auditing', 'auditing-ios', 'submit-device', 'get-sts', 'set-device-tokens', 'active', 'get-customer-info', 'click']
        ];
        return $behaviors;
    }

    public function actionGetBanner()
    {
        $query = BannerModel::find();
        $data = $query->select('article_id as id,url as image')
            ->where(['status' => 1])
            ->asArray()
            ->all();
        return common::response(1, '操作成功', $data);
    }

    // public function actionUploadBase64ImgAndToAliyun_oss()
    // {
    //     $type = $this->get('type') ?? '';
    //     $base64_image = Yii::$app->request->post('image') ?? '';
    //     if (!$base64_image) return common::response(0, '请上传图片');
    //     $pic = base64_decode($base64_image);
    //     $date =  Common::generateDatetime('Y-m-d');
    //     $upload_path =  "/mnt/upload/{$date}/";
    //     if (! is_dir($upload_path)) @mkdir($upload_path, 0757);
    //     $file_name = Yii::$app->security->generateRandomString() . '.png';
    //     $file_oss_path = $date . '/' . $file_name;
    //     $file_local_path = $upload_path . $file_name;
    //     file_put_contents($file_local_path, $pic);

    //     $ret = Common::uploadToAliyun_oss($type, $file_local_path, $file_oss_path);
    //     if ($ret['status']) {
    //         unlink($file_local_path);
    //         return common::response(1, '上传成功', $ret['info']['url']);
    //     }
    //     return common::response(0, '上传失败');
    // }

    public function actionUploadFileAndToAliyun_oss()
    {
        $type = Yii::$app->request->get('type') ?? '';
        $allowType = ['avatar'];
        if (!in_array($type, $allowType)) Common::messageReturn(0, '非法访问');
        $file = $_FILES['file'] ?? false;
        $date = Common::generateDatetime('Y-m-d');
        $upload = new Upload();
        $config = [
            'file' => $file,
            'savePath' => $date . '/',
            'fileTypeAllow' => ['gif', 'png', 'jpg', 'jpeg'],
            'maxSize' => 1024 * 1024 * 5,
        ];
        if (!$upload->load($config)) return Common::response(0, $upload->getError()['message']);

        $file_name = Yii::$app->security->generateRandomString() . '.' . pathinfo($file['name'])['extension'];
        $file_oss_path = $date . '/' . $file_name;
        $file_temp = $file['tmp_name'];
        $ret = Common::uploadToAliyun_oss($type, $file_temp, $file_oss_path);
        if ($ret['status']) {
            unlink($file_temp);
            return common::response(1, '上传成功', $ret['info']['url']);
        }
        return common::response(0, '上传失败');
    }

    public function actionAppCheckVersion()
    {
        $header = Yii::$app->request->headers;
        $name = $header->get('name');
        if (empty($name) || $name == 'xijin') {
            $seach = 'xijin';
        } elseif ($name == 'xijing') {
            $seach = 'xijing';
        }
        $get = $this->get;
        $last_app_version = AppVersionModel::find()
            ->where($get)
            ->andWhere(['name' => $seach])
            ->andWhere(['status' => 1])
            ->orderBy('id desc')
            ->one();
        return common::response(1, '', $last_app_version);
    }

    public function actionFeedback()
    {
        $feedback = new FeedbackModel();
        $post = [];
        $postdata = $this->post();
        $post['FeedbackModel'] = $postdata;
        if(empty($postdata['content'])){
            return Common::response(0, '内容不能为空');
        }
        if(!empty($postdata['user_id'])){
            $user = UserModel::find()->where(['id'=>$postdata['user_id']])->asArray()->one();
            $post['FeedbackModel']['mobile'] = $user['mobile'];
        }
        $post['FeedbackModel']['create_time'] = date('Y-m-d H:i:s');
        if ($feedback->load($post) && $feedback->save()) return Common::response(1, '提交成功');
        return Common::response(0, '提交失败', $feedback->getErrors());
    }

    public function actionGetFeedback()
    {
        $query = FeedbackModel::find()->orderBy('id desc');
        return Helper::usePage($query);
    }


    public function actionAuditing()
    {
        $searchModel = new AppVersionModelSearch();
        $search['AppVersionModelSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);

        return $dataProvider;
    }

    public function actionAuditingIos()
    {
        $version = $this->post('noisrev');
        $os = '2';
        $res = AppVersionModel::find()->where(['version' => $version, 'os' => $os, 'status' => 1])->one();
        if ($res) {
            if ($res->auditing) return common::response(1);
        }
        return common::response(0);
    }

    public function actionSubmitDevice()
    {
        $user_id = $this->post('user_id');
        if ($user_id) {
            $exist_user_id = UserDeviceModel::findAll(['user_id' => $user_id]);
            if ($exist_user_id) return common::response(1, 'success');
        }

        $device_token = $this->post('device_token');
        $exist_device = UserDeviceModel::findAll(['device_token' => $device_token]);
        if ($exist_device) return common::response(1, 'success');

        $device = new UserDeviceModel();
        $device->ip = ip2long(Common::getClientIp());
        $device->device_token = $device_token;
        $device->client_id = $this->post('client_id');
        $device->platform = $this->post('platform');
        $device->create_time = Common::generateDatetime();
        if (!$device->save()) return common::response(0, '', $device->getErrors());
        return common::response(1, 'success');
    }

    // 获取贷超url
    public function actionGetDc()
    {
        $data = AppSettingModel::find()->select('dc_url')->where(['id' => 1])->scalar();
        $data = $data ?: '';
        return common::response(1, 'success', $data);
    }

    public function actionGetSts()
    {
        $client_name = $this->get('client_name');
        $aliyun_sts = Yii::$app->params['aliyun_sts'];
        $region_id = $aliyun_sts['REGION_ID'];
        $endpoint = $aliyun_sts['ENDPOINT'];
        $access_token = $aliyun_sts['AccessKeyID'];
        $key = $aliyun_sts['AccessKeySecret'];
        $roleArn = $aliyun_sts['roleArn'];

        $aliyun_oss = Yii::$app->params['aliyun_oss'];
        $oss_end_point = $aliyun_oss['EndPoint'];
        $region = $aliyun_oss['REGION'];
        $oss_bucket = $aliyun_oss['Bucket'][0];
        $oss_url = "https://" . $oss_bucket . '.' . substr($oss_end_point, 7);

        DefaultProfile::addEndpoint($region_id, $region_id, "Sts", $endpoint);
        $profile = DefaultProfile::getProfile($region_id, $access_token, $key);
        $client = new DefaultAcsClient($profile);
        $request = new AssumeRoleRequest();
        $request->setRoleSessionName($client_name);
        $request->setRoleArn($roleArn);
        $request->setDurationSeconds(3600);

        try {
            $response = $client->getAcsResponse($request);
            $credentials = $response->Credentials;
            $credentials->endpoint = $oss_end_point;
            $credentials->Expiration = strtotime($credentials->Expiration);
            $credentials->bucket = $oss_bucket;
            $credentials->oss_url = $oss_url;
            $credentials->region = $region;
            return common::response(1, 'success', $credentials);
        } catch (ServerException $e) {
            return common::response(0, 'error', $e->getMessage());
        } catch (ClientException $e) {
            return common::response(0, 'error', $e->getMessage());
        }
    }

    public function actionHuanXinToken()
    {
        $huanxinParams = Yii::$app->params['huanxin'];
        $orgname = $huanxinParams['Orgname'];
        $appkey = $huanxinParams['AppKey'];
        $appName = $huanxinParams['appName'];
        $clientID = $huanxinParams['ClientID'];
        $clientSecret = $huanxinParams['ClientSecret'];
        $huanxin = new HuanXin($orgname, $appkey, $appName, $clientID, $clientSecret);
        // $huanxin->registerUser($mobile, $accessToken, $nickname);
        for ($i = 0; $i < 250; $i++) {
            $user = UserModel::find()->where(['id' => $i])->one();
            if ($user && !$user->huanxin_uuid) {
                $res = $huanxin->registerUser($user->mobile, $user->access_token, $user->mobile);
                if ($res->duration > 0) {
                    $entities = $res->entities[0];
                    $user->huanxin_uuid = $entities->uuid;
                    $user->huanxin_type = $entities->type;
                    $user->huanxin_created = $entities->created;
                    $user->huanxin_modified = $entities->modified;
                    $user->huanxin_username = $entities->username;
                    $user->huanxin_activated = (int)$entities->activated;
                    $user->huanxin_nickname = $entities->nickname;
                    $user->huanxin_password = $user->access_token;
                    $user->save();
                }
            }
        }
    }

    public function actionSetDeviceTokens()
    {
        $user_id = $this->post('user_id');
        $type = $this->post('type');  // 安卓 1 , IOS 2
        $device_id = $this->post('device_tokens');
        $model = UserModel::find()->where(['id' => $user_id])->one();
        if (!$model) return common::response(0, '未找到相关用户');
        $model->os = $type;
        $model->device_id = $device_id;  // device_tokens 设备token
        if ($model->save()) return common::response(1, '操作成功');
        return common::response(0, '操作成功', $model->getMessage());
    }

    public function actionCheckRepeatMobile()
    {
        $mobile = $this->post('mobile');
        $info = UserModel::find()->where(['mobile' => $mobile])->scalar();
        if ($info) return common::response(0, '手机号有重复', ['status' => 0]);
        return common::response(0, '手机号没有重复', ['status' => 1]);
    }


    public function actionActive()
    {
        $type = $this->get('type') ?? ''; // xijin sshua cqqb
        $device_type = $this->getDeviceType();
        if ($device_type == 'ios') {
            header("Location:https://apps.apple.com/cn/app/%E7%8A%80%E9%87%91/id1472961547");
        } else if ($device_type == 'android') {
            header("Location:https://a.app.qq.com/o/simple.jsp?pkgname=com.xiyi.rhinobillion");
        } else {
            header("Location:https://a.app.qq.com/o/simple.jsp?pkgname=com.xiyi.rhinobillion");
        }
    }

    protected function getDeviceType()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = "android";

        if (!$this->isMobile()) {
            $type = 'windows';
        }

        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $type = 'ios';
        }

        if (strpos($agent, 'android')) {
            $type = 'android';
        }

        return $type;
    }

    protected function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }


    // 获取客服的信息
    public function actionGetCustomerInfo()
    {
        $user_info = UserModel::find()->where(['mobile' => '18616731161'])->select('id,mobile,nick_name,avatar_image,huanxin_username,huanxin_uuid,huanxin_nickname,huanxin_password')->one();
        if (!$user_info) return null;
        return $user_info;
    }

    public function actionTest()
    {
        Common::send_active_msg('****', 'xijin');
    }

    /*
     * 犀金按钮点击
     * */
    public function actionClick()
    {
        $type = $this->get('type');
        if (empty($type)) return false;
        $redis = \Yii::$app->cache->redis;
        $time = time();
        $random = session_create_id() . uniqid();

        switch ($type) {
            case Config::CLICKSHARE:
                $redis->executeCommand('ZADD', [Config::CLICKINFO[Config::CLICKSHARE], $time, $random]);
                break;
            default:
                return false;
        }

        return true;
    }

}
