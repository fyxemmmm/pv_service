<?php

namespace api\controllers;

use common\models\AppImageModel;
use common\models\Common;
use service\controllers\CommonController;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * AppImageController implements the CRUD actions for AppImageModel model.
 */
class AppSwitchController extends CommonController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['check']
        ];
        return $behaviors;
    }

    /**
     * Lists all AppImageModel models.
     * @return mixed
     */
    public function actionCheck()
    {
        $key = $this->get('key', '');

        $value = Common::getAppSwitchKey($key);
        '' === $value && $value = 0;
        
        return $value;
    }

}
