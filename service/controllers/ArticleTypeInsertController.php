<?php

namespace service\controllers;

use yii\data\ActiveDataProvider;
use common\models\ArticleTypeInsertModel;
use common\models\Common;

class ArticleTypeInsertController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = ArticleTypeInsertModel::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $info = $this->findModel($id);
        return Common::response(1, 'Success', $info);
    }

    public function actionCreate(){
        $input = $this->post();
        $model = new ArticleTypeInsertModel();
        $model->setAttributes($input);
        $model->save();
        return Common::response(1, 'success');
    }

    public function actionUpdate($id){
        $model = $this->findModel($id);
        $model->setAttributes($this->post());
        $model->save();
        return Common::response(1, 'success');
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) return Common::response(1, '删除成功', $model);
        return Common::response(0, '删除失败');
    }


    protected function findModel($id)
    {
        if (($model = ArticleTypeInsertModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

