<?php

namespace service\controllers;

use api\models\User;
use service\models\Admin;
use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\Common;
use yii\filters\Cors;
use common\models\AliyunOss;
use common\VarTmp;

class CommonController extends ActiveController
{
    public $adminId;
    public $post;
    public $get;
    public $modelClass = '';
    public $controller_id;
    public $action_id;
    public $params;
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function init()
    {
        parent::init();
        $this->get = Yii::$app->request->get();
        $this->post = Yii::$app->request->post();
        $this->params = Yii::$app->params;
        $this->adminId = $this->getAdminId();    
    }

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        $this->controller_id = Yii::$app->controller->id;
        $this->action_id = Yii::$app->controller->action->id;
        $res = Yii::$app->response;
        $res->on('beforeSend', function ($event) {
            $response = $event->sender;
            $is_expire = $this->checkExpireTime($this->controller_id, $this->action_id, $this->adminId);
//            $is_expire = false;
            if($is_expire){  // 如果已经过期
                $response->data = [
                    'code' => 401,
                    'message' => '您登录已过期,请重新登录',
                    'info' => '',
                ];
            }else{
                if (! isset($response->data['code'])) {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                    if ($response->statusCode == 200) {
                        $response->data = [
                            'code' => 1,
                            'message' => 'success',
                            'info' => $response->data,
                        ];
                        if(VarTmp::$extra) $response->data['info']['extra'] = VarTmp::$extra; // 额外字段
                    } else {
                        $response->data = [
                            'code' => $response->data['code'] ?? 0,
                            'message' => $response->data['message'] ?? '',
                            'info' => $response->data['info'] ?? '',
                        ];
                    }
                }
            }

            // 写api日志
            VarTmp::$admin_id = $this->adminId;
            common::apiLog($this->controller_id, $this->action_id, 2);
        });
        
        return true;
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
        ];

        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://localhost:8083'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Origin' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [],
            ],
        ];
        return $behaviors;
    }


    protected function checkExpireTime($controller_id, $action_id, $admin_id){
        if(empty($admin_id)) return false;
        if($controller_id == 'admin' && $action_id == 'get-access-token') return false;
        if($controller_id == 'admin' && $action_id == 'send-sms-code') return false;

        $redis = Yii::$app->cache;

        $login_time = $redis->get('admin_login_'.$admin_id);

        if($login_time){
            if($login_time + 60*60*8 - time() < 0){
                $redis->delete('admin_login_'.$admin_id);
                return true; // 过期
            }else{
                return false; // 没有过期
            }
        }else{
            $login_time = time();
            $redis->set('admin_login_'.$admin_id,$login_time);
            return false;
        }
    }

    /**
     * 错误处理
     *
     * @return void ErrorException
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->asJson($exception);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $param
     * @param [type] $set
     * @return void
     */
    public function post($param = "", $set = null)
    {
        $postParams = Yii::$app->request->post();
        if ($param === "") return $postParams;
        if (Yii::$app->request->post($param) === null) return $set;
        return Yii::$app->request->post($param);
    }

    /**
     * Undocumented function
     *
     * @param string $param
     * @param [type] $set
     * @return void
     */
    public function get($param = "", $set = null)
    {
        $getParams = Yii::$app->request->get();
        if ($param === "") return $getParams;
        if (Yii::$app->request->get($param) === null) return $set;
        return Yii::$app->request->get($param);
    }
    /**
     * getUserInfo function
     *
     * @return void
     */
    public function getUserInfo()
    {
//        if (! $this->isNotLogin()) Common::messageReturn(0, '未登录');
        $access_token = $this-> getAccessToken();
        return Admin::findOne(['access_token' => $access_token]);
    }

    public function isNotLogin()
    {
        $authorization = Yii::$app->request->headers['authorization'];
        if ($authorization) return true;
        return false;
    }

    public function getAccessToken() 
    {
        if (! $this->isNotLogin()) return null;
        $authorization = Yii::$app->request->headers['authorization'];
        $access_token = explode(" ", $authorization)[1];
        return $access_token;
    }

    public function getAdminId()
    {
        $user_info = $this->getUserInfo();
        return $user_info['id'];
    }

    public function filterWhere($query, $params = []) 
    {
        if (! $params) return $query;
        foreach ($params as $key => $value) {
            $arr = explode(',', $value);
            $query->andFilterWhere([
                $key => $arr,
            ]);
        }
        return $query;
    }

    public function actionUploadToAliyun_oss()
    {
        $type = $this->get('type');
        $params = $this->params;
        $oss_path = $params['oss_path'][$type] . Common::generateDatetime('Y-m-d') . '/';
        if (! $oss_path) $oss_path = 'others/';
        $file = $_FILES['file'];
        if ($file['error'] !== 0) return Common::response(0, '文件出错');
        if ($file['size'] > 1024 * 1024 * 0.5) return Common::response(0, '图片不能超过500kb');
        
        $file_name = $oss_path . $file['name'];
        $file_temp = $file['tmp_name'];
        $aliyun_oss = new AliyunOss();
        $result = $aliyun_oss->uploadFile($file_name, $file_temp);
        if (! $result) return Common::response(0, '阿里云上传错误');
        $result['info']['url'] = 'https' . substr($result['info']['url'], 4);
        return Common::response(1, $file_name, $result['info']);
    }

}
