<?php

namespace service\controllers;

use common\models\BusinessTypeModel;
use common\models\Common;
use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;


/**
 * ArticleController implements the CRUD actions for Article model.
 */
class BusinessTypeController extends CommonController
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
        $dataProvider = new ActiveDataProvider([
            'query' => $query = BusinessTypeModel::find()
        ]);

        return $dataProvider;
    }

    /**
     * Displays a single Article model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $info = BusinessTypeModel::findOne($id);

        return Common::response(1, 'success', $info);
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = BusinessTypeModel::findOne($id);
        $post = Yii::$app->request->post();

        $model->setAttributes($post);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BusinessTypeModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessTypeModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
