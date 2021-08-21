<?php

namespace service\controllers;
use service\models\RadioShaftSearch;
use common\models\RadioShaftModel;
use common\models\Common;
use Yii;


class RadioShaftController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new RadioShaftSearch();
        $search['RadioShaftSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionCreate(){
        $post = Yii::$app->request->post();
        $model = new RadioShaftModel();
        $model->setAttributes($post);
        if($model->save()){
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionUpdate($id){
        $model = RadioShaftModel::findOne($id);
        $post = Yii::$app->request->post();
        $model->setAttributes($post);
        if($model->save()){
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionDelete($id){
        $model = RadioShaftModel::findOne($id);
        if( $model->delete()){
            return Common::response(1, '删除成功');
        }
        return Common::response(1, '删除失败');
    }
    
}
