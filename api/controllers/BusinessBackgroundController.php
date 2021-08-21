<?php

namespace api\controllers;

use common\models\BusinessBackgroundModel;
use service\controllers\CommonController;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * CityController implements the CRUD actions for CityModel model.
 */
class BusinessBackgroundController extends CommonController
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
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /**
     * Lists all CityModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = BusinessBackgroundModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sort_num' => SORT_ASC,
                    'id' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => 888
            ]
        ]);

        return $dataProvider;
    }

}
