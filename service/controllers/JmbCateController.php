<?php

namespace service\controllers;
use common\models\JmbCategoryModel;
use yii\data\ActiveDataProvider;
use common\models\Common;

class JmbCateController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = JmbCategoryModel::find()->select('id,name,image_url')->where(['pid' => 0]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function actionView($id)
    {
        $query = JmbCategoryModel::find()->select('id,name,image_url')->where(['id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function actionCreate()
    {
        $post = $this->post();
        $model = new JmbCategoryModel();
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }


    public function actionUpdate($id){
        $post = $this->post();
        $model = JmbCategoryModel::findOne($id);
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionDelete($id){
        $has_child = JmbCategoryModel::find()->where(['pid' => $id])->one();
        if($has_child) return Common::response(0, '该分类下有子分类,请先删除子分类');
        $model = JmbCategoryModel::find()->where(['id' => $id])->one();
        if($model->delete()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }



}


