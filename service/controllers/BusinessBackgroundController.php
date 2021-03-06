<?php

namespace service\controllers;

use common\models\BusinessBackgroundModel;
use common\models\BusinessModel;
use common\models\Common;
use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;


/**
 * ArticleController implements the CRUD actions for Article model.
 */
class BusinessBackgroundController extends CommonController
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

    /**
     * Displays a single Article model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $info = BusinessBackgroundModel::findOne($id);

        return Common::response(1, 'success', $info);
    }

    public function actionCreate()
    {
        $model = new BusinessBackgroundModel();
        $post = Yii::$app->request->post();

        $model->setAttributes($post);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
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
        $model = BusinessBackgroundModel::findOne($id);
        $post = Yii::$app->request->post();

        $model->setAttributes($post);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionDelete($id)
    {
        $check_nums = BusinessModel::find()
            ->where(['b_id' => $id])
            ->count();

        if ($check_nums) {
            return Common::response(0, "?????????????????????{$check_nums}?????????");
        } else {
            BusinessBackgroundModel::findOne($id)->delete();
            return Common::response(1, 'success');
        }
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BusinessBackgroundModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessBackgroundModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
