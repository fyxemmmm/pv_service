<?php

namespace api\controllers;

use api\models\ArticleReport;
use Yii;
use api\models\ArticleReportSearch;
use common\models\Common;


class ArticleReportController extends CommonController
{
    public $modelClass = '';


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new ArticleReportSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['ArticleReportSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    public function actionCreate()
    {
        $article_id = $this->post('article_id');
        $content = $this->post('content') ?? '';
        $model = new ArticleReport();
        $data = [
            'user_id' => $this->userId,
            'article_id' => $article_id,
            'content' => $content
        ];
        $model->attributes = $data;
        $model->save();
        return Common::response(1,  '操作成功');
    }


}
