<?php

namespace service\controllers;

use common\models\BusinessPicModel;
use common\models\Common;
use Yii;
use service\models\Business;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * BusinessController implements the CRUD actions for BusinessModel model.
 */
class BusinessController extends CommonController
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /**
     * Lists all BusinessModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Business::find()
            ->where(['is_del' => 0])
            ->orderBy('`id` DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    /**
     * Displays a single BusinessModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Creates a new BusinessModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Business();
        $post = Yii::$app->request->post();
        $post['end_time'] = date('Y-m-01', strtotime($post['end_time']));

        $model->setAttributes($post);
        if ($model->save()) {
            if (isset($post['pics']) && $post['pics']) {
                $id = $model->id;
                array_walk($post['pics'], function($item) use ($id) {
                    $BusinessPicModel = new BusinessPicModel();
                    $BusinessPicModel->setAttributes([
                        'b_id' => $id,
                        'img_url' => $item
                    ]);
                    $BusinessPicModel->save();
                });
            }
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Updates an existing BusinessModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        $post['end_time'] = date('Y-m-01', strtotime($post['end_time']));

        $model->setAttributes($post);
        if ($model->save()) {
            $id = $model->id;
            BusinessPicModel::deleteAll(['b_id' => $id]);

            if (isset($post['pics']) && $post['pics']) {
                array_walk($post['pics'], function($item) use ($id) {
                    $BusinessPicModel = new BusinessPicModel();
                    $BusinessPicModel->setAttributes([
                        'b_id' => $id,
                        'img_url' => $item
                    ]);
                    $BusinessPicModel->save();
                });
            }
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Deletes an existing BusinessModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->setAttributes(['is_del' => 1]);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionSetPrivate()
    {
        $id = $this->post('id');
        $private = $this->post('is_pri');

        $model = $this->findModel($id);
        $model->setAttributes(['is_pri' => $private]);
        !$private && $model->updateCounters(['interested_nums' => rand(1, 49)]);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Finds the BusinessModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Business the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Business::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
