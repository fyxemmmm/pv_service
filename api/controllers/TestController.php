<?php

namespace api\controllers;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * 测试
 */
class TestController extends CommonController
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
            'except' => ['index']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        try{
            // 待支付自动取消
            $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
            $client->connect('127.0.0.1', 9510);
            $data = 36;
            $data = pack('N', strlen($data)) . $data;
            $client->send($data);
        }catch (\Exception $e){
            // pass
        }
    }

}
