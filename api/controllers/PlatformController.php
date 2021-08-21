<?php

namespace api\controllers;

use common\models\Common;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\PlatformModel;


class PlatformController extends CommonController
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
            'except' => ['index','get-download-url']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionGetDownloadUrl()
    {
        $name = $this->get('name');  // 渠道名 与vivo 小米等同级别
        $apk_url = PlatformModel::find()->select('apk_url')->filterWhere(['name' => $name])->scalar();
        if(!$apk_url) return Common::response(0, '未找到下载地址');
        return ['apk_url' => $apk_url];
    }


}
