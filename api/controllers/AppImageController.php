<?php

namespace api\controllers;

use Yii;
use common\models\AppImageModel;
use yii\data\ActiveDataProvider;
use service\controllers\CommonController;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AppImageController implements the CRUD actions for AppImageModel model.
 */
class AppImageController extends CommonController
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
            'except' => ['list']
        ];
        return $behaviors;
    }

    /**
     * Lists all AppImageModel models.
     * @return mixed
     */
    public function actionList()
    {
        $key_list = $this->get('key_list', '');

        $obj = AppImageModel::find();
        $obj->where(['in', 'key', $key_list]);

        return $obj->asArray()->all();
    }

}
