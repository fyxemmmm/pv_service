<?php

namespace service\controllers;
use common\models\JmbCategoryModel;
use common\models\JmbModel;
use yii\data\ActiveDataProvider;
use common\models\Common;
use service\models\JmbSubCate;

class JmbSubCateController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $query = JmbSubCate::find()->where(['<>','pid',0]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function actionView($id)
    {
        $query = JmbSubCate::find()->where(['id' => $id])->andWhere(['<>','pid',0]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    /*
     * 二级分类添加
     * */
    public function actionCreate(){
        $pid = $this->post('pid');
        $is_exist = JmbCategoryModel::find()->where(['id' => $pid, 'pid' => 0]);
        if(!$is_exist) return Common::response(0, '不存在的上级分类');
        $name = $this->post('name');
        $image_url = $this->post('image_url');
        $model = new JmbCategoryModel();
        $model->pid = $pid;
        $model->name = $name;
        $model->image_url = $image_url;
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionUpdate($id){
        $post = $this->post();
        $model = JmbCategoryModel::findOne($id);
        $pid = $post['pid'] ?? '';
        if(!$pid) return Common::response(0, '请填写pid');
        $check = JmbCategoryModel::find()->where(['id' => $pid])->one();
        if(!$check || $check['pid'] != 0)  return Common::response(0, '请填写pid的值为一级分类的id');
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionDelete($id){
        $is_in_use = JmbModel::find()->where(['jmb_category_id' => $id])->one();
        if($is_in_use) return Common::response(0, '有加盟宝正在使用该分类,无法删除');
        $model = JmbCategoryModel::find()->where(['id' => $id])->one();
        if($model->delete()) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }

}
