<?php

namespace service\controllers;

use common\models\Common;
use Yii;
use common\models\LabelModel;
use common\models\LabelModelSearch;
use common\models\ArticleLabelModel;
use yii\web\NotFoundHttpException;

/**
 * ArticleKeywordController implements the CRUD actions for ArticleKeywordModel model.
 */
class LabelController extends CommonController
{
    public $modelClass = 'common\models\LabelModel';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new LabelModelSearch();
        $search['LabelModelSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);

        return $dataProvider;
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        if(!is_array($request)){
            $is_repeat = LabelModel::find()->where(['name' => Yii::$app->request->post('name')])->one();
            if($is_repeat) return;
            $model = new LabelModel();
            $model->setAttributes(Yii::$app->request->post());
            if ($model->save()) return Common::response(1, 'success', $model);
        }else{
            foreach ($request['name'] as $k=>$v){
                if(is_int($v)) continue;
                $is_repeat = LabelModel::find()->where(['name' => $v])->one();
                if($is_repeat) continue;
                $model = new LabelModel();
                $model->name = $v;
                $model->save();
            }
            return Common::response(1, 'success');
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setAttributes(Yii::$app->request->post());

        if ($model->save()) return Common::response(1, 'success', $model);
        return Common::response(0, 'failure', $model->getErrors());
    }

    public function actionView($id)
    {
        $info = $this->findModel($id);
        return Common::response(1, 'Success', $info);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $data = ArticleLabelModel::find()->where(['label_id' => $id])->one();
        if($data){
            return Common::response(0, '请先删除这个标签对应的相关文章');
        }
        if ($model->delete()) return Common::response(1, '删除成功', $model);
        return Common::response(0, '删除失败');
    }

    protected function findModel($id)
    {
        if (($model = LabelModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
