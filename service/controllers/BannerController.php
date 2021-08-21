<?php

namespace service\controllers;

use common\models\BannerModelSearch;
use common\models\Common;
use Yii;
use common\models\BannerModel;
use yii\web\NotFoundHttpException;

/**
 * BannerController implements the CRUD actions for BannerModel model.
 */
class BannerController extends CommonController
{
    public $modelClass = 'common\models\BannerModel';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    /**
     * Lists all BannerModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BannerModelSearch();
        $search['BannerModelSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);

        return $dataProvider;
    }

    /**
     * Displays a single BannerModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $info = $this->findModel($id);
        return Common::response(1, 'Success', $info);
    }

    /**
     * Creates a new BannerModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BannerModel();
        $post = Yii::$app->request->post();
        $post['create_time'] = date('Y-m-d H:i:s');

        $model->setAttributes($post);

        if ($model->save()) return Common::response(1, 'success', $model);
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Updates an existing BannerModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        $post['update_time'] = date('Y-m-d H:i:s');

        $model->setAttributes($post);

        if ($model->save()) return Common::response(1, 'success', $model);
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Deletes an existing BannerModel model.
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
     * Finds the BannerModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BannerModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BannerModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
