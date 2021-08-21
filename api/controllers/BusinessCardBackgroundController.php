<?php

namespace api\controllers;

use common\models\BusinessCardBackgroundModel;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;


/**
 * ArticleController implements the CRUD actions for Article model.
 */
class BusinessCardBackgroundController extends CommonController
{
    public $modelClass = 'common\models\Activity';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /**
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = BusinessCardBackgroundModel::find();

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

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BusinessCardBackgroundModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessCardBackgroundModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
