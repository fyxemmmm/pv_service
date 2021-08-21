<?php

namespace service\controllers;

use common\models\AppSettingModel;
use common\models\Common;
use Yii;
use common\models\WeekNewsModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * WeekNewsController implements the CRUD actions for WeekNewsModel model.
 */
class WeekNewsController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /**
     * Lists all WeekNewsModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = WeekNewsModel::find()
            ->orderBy('sort');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    /**
     * Displays a single WeekNewsModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Creates a new WeekNewsModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WeekNewsModel();
        $post = Yii::$app->request->post();
        $model->setAttributes($post);
        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Updates an existing WeekNewsModel model.
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
     * Deletes an existing WeekNewsModel model.
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

    public function actionGetLogo()
    {
        return AppSettingModel::find()
            ->select('week_news_logo')
            ->where(['id' => 1])
            ->asArray()
            ->one();
    }

    public function actionUpdateLogo()
    {
        $logo = $this->post('week_news_logo');

        $model = AppSettingModel::findOne(1);
        $model->setAttribute('week_news_logo', $logo);
        $model->save();

        return Common::response(1, 'success');
    }

    /**
     * Finds the WeekNewsModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WeekNewsModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WeekNewsModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
