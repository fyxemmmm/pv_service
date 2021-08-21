<?php

namespace common\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\base\ErrorException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\Common;
use api\models\User;
use yii\filters\Cors;

class BaseController extends ActiveController
{
    public $modelClass;
    public $controller_id;
    public $action_id;
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        $this->controller_id = Yii::$app->controller->id;
        $this->action_id = Yii::$app->controller->action->id;
        $restful = ['index', 'view'];
        if (in_array($this->action_id, $restful)) {
            $res = Yii::$app->response;
            $res->on('beforeSend', function ($event) {
                $response = $event->sender;
                if ($response->statusCode == 200) {
                    $response->data = [
                        'code' => 1,
                        'message' => 'success',
                        'info' => $response->data,
                    ];
                } else {
                    $response->data = [
                        'code' => $response->data['code'],
                        'message' => $response->data['message'],
                        'info' => $response->data['info'],
                    ];
                }
                common::apiLog($this->controller_id, $this->action_id, 1);
            });
        }
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
                'Origin' => ['*'],
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
        $authorization = Yii::$app->request->headers['authorization'];
        if ($authorization) Common::messageReturn(0, '未登录');
        $access_token = explode(" ", $authorization)[1];
        return User::findOne(['access_token' => $access_token, 'status' => 1]);
    }
}