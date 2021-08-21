<?php

namespace service\controllers;

use common\models\Common;
use common\models\MakeMoneyGroupModel;
use common\models\MakeMoneyTypeModel;
use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;


/**
 * ArticleController implements the CRUD actions for Article model.
 */
class MakeMoneyTypeController extends CommonController
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
        $query = MakeMoneyTypeModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
        $info = MakeMoneyTypeModel::findOne($id);

        return Common::response(1, 'success', $info);
    }

    public function actionCreate()
    {
        $model = new MakeMoneyTypeModel();
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
        $model = MakeMoneyTypeModel::findOne($id);
        $post = Yii::$app->request->post();

        $model->setAttributes($post);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionDelete($id)
    {
        $check_nums = MakeMoneyGroupModel::find()
            ->where(['type_id' => $id])
            ->count();

        if ($check_nums) {
            return Common::response(0, "当前背景图已被{$check_nums}个群使用");
        } else {
            MakeMoneyTypeModel::findOne($id)->delete();
            return Common::response(1, 'success');
        }
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MakeMoneyTypeModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MakeMoneyTypeModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
