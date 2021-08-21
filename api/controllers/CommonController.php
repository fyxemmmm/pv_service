<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\Common;
use api\models\User;
use yii\filters\Cors;
use common\VarTmp;
use common\Extra;

class CommonController extends ActiveController
{
    public $modelClass;
    public $controller_id;
    public $action_id;
    public $post;
    public $get;
    public $params;
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public $userId;

    public function init()
    {
        parent::init();
        $this->setPage(); // 设置分页信息
        $this->get = Yii::$app->request->get();
        $this->post = Yii::$app->request->post();
        $this->params = Yii::$app->params;
        $this->userId = $this->getUserId();
        Yii::$app->params['__web'] = [
            'user_id' => $this->userId,
        ];
    }

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        $this->controller_id = Yii::$app->controller->id;
        $this->action_id = Yii::$app->controller->action->id;
        $res = Yii::$app->response;
        $res->on('beforeSend', function ($event) {
            $response = $event->sender;
            $response->format = \yii\web\Response::FORMAT_JSON;
            if(VarTmp::$json_force_object){
                $response->formatters['json']['encodeOptions'] = 16; // JSON_FORCE_OBJECT
            }
            if (! isset($response->data['code'])) {
                if ($response->statusCode == 200) {
                    // 修改最终数据
                    $extra = new Extra($this->controller_id, $this->action_id, $response->data);
                    $changed_data = $extra->checkFinalData();
                    if($changed_data){
                        $response->data['items'] = $changed_data;
                    }
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
            // 写api日志
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
                // 'Origin' => ['*'],
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

    public function setPage(){
        $page = $this->get('page') ?? '';
        $per_page = $this->get('per-page') ?? '';
        if($page) VarTmp::$page = $page;
        if($per_page) VarTmp::$per_page = $per_page;
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
        if (! $this->isNotLogin()) return null;
        $access_token = $this-> getAccessToken();
        return User::findOne(['access_token' => $access_token]);
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

    public function getUserId()
    {
        $user_info = $this->getUserInfo();
        return $user_info['id'];
    }
}