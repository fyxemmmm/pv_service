<?php

namespace service\controllers;

use api\models\MakeMoneyGroup;
use common\models\Common;
use Yii;
use common\models\MakeMoneyGroupModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * MakeMoneyGroupController implements the CRUD actions for MakeMoneyGroupModel model.
 */
class MakeMoneyGroupController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete'], $actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = MakeMoneyGroup::find();
        $query->orderBy('`create_time` DESC, `id` DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }

    /**
     * Updates an existing MakeMoneyGroupModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = MakeMoneyGroupModel::findOne(['id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $post = Yii::$app->request->post();
        $model->setAttributes($post);

        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

}
