<?php

namespace api\controllers;

use Yii;
use common\models\MakeMoneyBannerModel;
use yii\data\ActiveDataProvider;
use service\controllers\CommonController;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MakeMoneyBannerController implements the CRUD actions for MakeMoneyBannerModel model.
 */
class MakeMoneyBannerController extends CommonController
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
            'except' => ['index']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * Lists all MakeMoneyBannerModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => MakeMoneyBannerModel::find(),
        ]);

        return $dataProvider;
    }

}
