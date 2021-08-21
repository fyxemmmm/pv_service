<?php

namespace service\controllers;

use common\models\GoodsModel;
use common\models\GoodsTypeModel;
use common\models\Common;

class GoodsTypeController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $data = GoodsTypeModel::find()->orderBy('order asc')->asArray()->all();
        foreach ($data as $k=>&$v){
            $v['id'] = (int)$v['id'];
        }
        return ['items' => $data];
    }

    public function actionView($id)
    {
        $data = GoodsTypeModel::find()->where(['id' => $id])->asArray()->all();
        foreach ($data as $k=>&$v){
            $v['id'] = (int)$v['id'];
        }
        return ['items' => $data];
    }

    public function actionCreate()
    {
        $post = $this->post();
        $model = new GoodsTypeModel();
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0,'failure', $model->getErrors());
    }

    public function actionUpdate($id)
    {
        $post = $this->post();
        $model = GoodsTypeModel::find()->where(['id' => $id])->one();
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0,'failure', $model->getErrors());
    }

    public function actionDelete($id){
        $model = GoodsTypeModel::find()->where(['id' => $id])->one();
        $has_used = GoodsModel::find()->where(['goods_type_id' => $id])->one();
        if($has_used)  return Common::response(0,'有商品正在使用该类型,无法删除');
        if($model->delete()) return Common::response(1, 'success');
        return Common::response(0,'failure', $model->getErrors());
    }

}
