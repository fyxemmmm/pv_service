<?php

namespace api\controllers;

use api\models\BusinessCard;
use common\models\Common;
use Yii;
use common\models\BusinessCardModel;
use service\controllers\CommonController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * BusinessCardController implements the CRUD actions for BusinessCardModel model.
 */
class BusinessCardController extends CommonController
{
    public $modelClass = '';

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
        $user_info = $this->getUserInfo();

        $query = BusinessCard::find()
            ->where(['u_id' => $user_info['id']]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 888
            ]
        ]);
    }

    /**
     * Displays a single BusinessCardModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Creates a new BusinessCardModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BusinessCardModel();
        $attributes = Yii::$app->request->post();

        $user_info = $this->getUserInfo();
        $attributes['u_id'] = $user_info['id'];

        $model->setAttributes($attributes);

        if ($model->save()) return Common::response(1, '创建成功', $model);
        return Common::response(0, '创建失败', $model->getErrors());
    }

    /**
     * Updates an existing BusinessCardModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $attributes = Yii::$app->request->post();
        $model->setAttributes($attributes);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Deletes an existing BusinessCardModel model.
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
     * Finds the BusinessCardModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BusinessCardModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessCardModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
