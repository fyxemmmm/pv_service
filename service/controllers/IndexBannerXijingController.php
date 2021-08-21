<?php

namespace service\controllers;

use common\models\Common;
use Yii;
use common\models\IndexBannerXijingModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * IndexBannerXijingController implements the CRUD actions for IndexBannerXijingModel model.
 */
class IndexBannerXijingController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete'], $actions['view']);
        return $actions;
    }

    /**
     * Lists all IndexBannerXijingModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = IndexBannerXijingModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    /**
     * Displays a single IndexBannerXijingModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Creates a new IndexBannerXijingModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new IndexBannerXijingModel();
        $post = Yii::$app->request->post();
        $model->setAttributes($post);
        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Updates an existing IndexBannerXijingModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        $model->setAttributes($post);
        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Deletes an existing IndexBannerXijingModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return Common::response(1, 'success');
    }

    /**
     * Finds the IndexBannerXijingModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return IndexBannerXijingModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IndexBannerXijingModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
