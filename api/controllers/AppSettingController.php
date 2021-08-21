<?php

namespace api\controllers;

use common\models\Common;
use Yii;
use common\models\AppSettingModel;
use common\models\AppSettingModelSearch;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;

/**
 * AppSettingsController implements the CRUD actions for AppSettingsModel model.
 */
class AppSettingController extends CommonController
{
    public $modelClass = 'common\models\AppSettingModel';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index', 'view', 'activity-url']
        ];
        return $behaviors;
    }


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    /**
     * Lists all AppSettingsModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AppSettingModelSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['AppSettingSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $info = AppSettingModel::findOne(1);

        return common::response(1, 'success', $info);
    }

    public function actionActivityUrl()
    {
        $info = AppSettingModel::findOne(1);

        return common::response(1, 'success', $info);
    }

    /**
     * Finds the AppSettingsModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AppSettingsModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AppSettingModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
