<?php

namespace service\controllers;

use service\models\Suggestion;
use service\models\SuggestionSearch;
use common\models\SuggestionPicModel;
use common\models\Common;
use Yii;
use yii\web\NotFoundHttpException;


/*
 * 反馈管理
 * */
class SuggestionController extends CommonController
{


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        $searchModel = new SuggestionSearch();
        $search['SuggestionSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionCreate(){

        echo 2;exit;
    }


    public function actionView()
    {
        $searchModel = new SuggestionSearch();
        $search['SuggestionSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        SuggestionPicModel::deleteAll(['suggestion_id'=>$id]);
        return Common::response(1, '删除成功');
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $data = Yii::$app->request->post();
        $model->setAttributes($data);
        if($model->save()) return Common::response(1, '更新成功');
        return Common::response(0, '更新失败');
    }

    protected function findModel($id)
    {
        if (($model = Suggestion::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
