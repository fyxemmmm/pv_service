<?php

namespace service\controllers;
use common\models\JmbHotCategoryModel;
use common\models\Common;
use yii\data\ActiveDataProvider;
use service\models\JmbHotPrefecture;


class JmbHotPrefectureController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = JmbHotPrefecture::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $query = JmbHotPrefecture::find()->where(['id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionCreate(){
        $post = $this->post();
        $model = new JmbHotCategoryModel();
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, '创建成功');
        return Common::response(0, '创建失败', $model->getErrors());
    }

    public function actionUpdate($id){
        $post = $this->post();
        $model = JmbHotCategoryModel::find()->where(['id' => $id])->one();
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, '更新成功');
        return Common::response(0, '更新失败', $model->getErrors());
    }

    public function actionDelete($id){
        $model = JmbHotCategoryModel::find()->where(['id' => $id])->one();
        if($model){
            $model->delete();
            return Common::response(1, '删除成功');
        }
        return Common::response(0, '删除失败', $model->getErrors());
    }

}
