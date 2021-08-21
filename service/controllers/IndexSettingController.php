<?php

namespace service\controllers;

use common\models\Common;
use Yii;
use common\models\IndexSettingModel;
use yii\web\NotFoundHttpException;

/**
 * IndexSettingController implements the CRUD actions for IndexSettingModel model.
 */
class IndexSettingController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update']);
        return $actions;
    }

    /**
     * Lists all IndexSettingModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->findModel(1);
    }

    /**
     * Updates an existing IndexSettingModel model.
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
     * Finds the IndexSettingModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return IndexSettingModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IndexSettingModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
