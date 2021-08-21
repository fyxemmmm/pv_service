<?php

namespace service\controllers;

use common\Config;
use Yii;
use common\models\AppVersionModel;
use common\models\Common;
use common\models\Upload;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

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
            'except' => ['']
        ];
        return $behaviors;
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
        $file = $_FILES['file'] ?? false;
        $date = Common::generateDatetime('Y-m-d');
        $upload = new Upload();
        $config = [
            'file' => $file,
            'savePath' => $date . '/',
            'fileTypeAllow' => ['gif', 'png', 'jpg', 'jpeg'],
            'maxSize' => 1024 * 1024 * 5,
        ];
        if (! $upload->load($config)) return Common::response(0, $upload->getError()['message']);

        $file_name = Yii::$app->security->generateRandomString() . '.' . pathinfo($file['name'])['extension'];
        $file_oss_path = $date . '/' . $file_name;
        $file_temp = $file['tmp_name'];
        $ret = Common::uploadToAliyun_oss($type, $file_temp, $file_oss_path);
        if ($ret['status']) {
            unlink($file_temp);
            return common::response(1, '上传成功', $ret['info']);
        }
        return common::response(0, '上传失败');
    }

    public function actionAppCheckVersion()
    {
        $get = $this->get;
        $last_app_version = AppVersionModel::find()
            ->where($get)
            ->andWhere(['status' => 1])
            ->orderBy('id desc')
            ->one();
        return common::response(1, '', $last_app_version);
    }

    public function actionGetBaseUrl(){
        return Yii::$app->request->getHostInfo();
    }


    public function actionGetClickInfo(){
        $start_time = $this->get('start_time');
        $end_time = $this->get('end_time');
        $start_time = empty($start_time) ? '-inf' : strtotime(date('Y-m-d 00:00:00',strtotime($start_time)));
        $end_time = empty($end_time) ? '+inf' : strtotime(date('Y-m-d 23:59:59',strtotime($end_time)));
        $redis = \Yii::$app->cache->redis;
        $list = [];
        foreach (Config::CLICKINFO as $type => $key_name){
            if($start_time =='-inf' && $end_time == '+inf'){
                $count = $redis->executeCommand('ZCARD',[$key_name]);
            }else{
                $type_count = $redis->executeCommand('ZRANGEBYSCORE',[$key_name,$start_time,$end_time]);
                $count = count($type_count);
            }
            $arr['type'] = $type;
            $arr['count'] = (int)$count;
            switch ($type){
                case Config::CLICKSHARE:
                    $arr['name'] = '个人中心分享点击次数';
                    break;
            }
            $list[] = $arr;
        }
        return $list;
    }

}
