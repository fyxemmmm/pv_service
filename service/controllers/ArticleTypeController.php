<?php

namespace service\controllers;

use common\models\ArticleModel;
use common\models\Common;
use Yii;
use common\models\ArticleTypeModel;
use common\models\ArticleTypeModelSearch;
use yii\web\NotFoundHttpException;

/**
 * ArticleTypeController implements the CRUD actions for ArticleTypeModel model.
 */
class ArticleTypeController extends CommonController
{
    public $modelClass = 'common\models\ArticleType';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new ArticleTypeModelSearch();
        $search['ArticleTypeModelSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);

        return $dataProvider;
    }

    public function actionCreate()
    {
        $model = new ArticleTypeModel();
        $model->setAttributes(Yii::$app->request->post());

        if ($model->save()) return Common::response(1, 'success', $model);
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
        $articles_count = ArticleModel::find()
            ->where(['type' => $id])
            ->count();

        if ($articles_count) {
            return Common::response(0, "该类型下还有{$articles_count}篇文章，请修改相关文章后再删除");
        }

        $model = $this->findModel($id);
        $model->setAttribute('is_del', 1);

        if ($model->save()) return Common::response(1, '删除成功');
        return Common::response(0, '删除失败');
    }

    protected function findModel($id)
    {
        if (($model = ArticleTypeModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
