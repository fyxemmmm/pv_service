<?php

namespace service\controllers;

use yii\data\ActiveDataProvider;
use common\models\ArticleDcDescModel;
use common\models\Common;


class ArticleDcDescController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /*
     * 获得贷超文章的信息
     * */
    public function actionIndex()
    {
        $query = ArticleDcDescModel::find()->select('id,content')->where(['type' => 0]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $query = ArticleDcDescModel::find()->select('id,content')->where(['type' => 0, 'id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionCreate()
    {
        $model = new ArticleDcDescModel();
        $model->content = $this->post('content');
        $model->type = 0; // 文章
        $model->article_id = 0; // 默认
        if($model->save()) return Common::response(1, '添加成功');
        return Common::response(0, '添加失败', $model->getErrors());
    }

    public function actionUpdate($id)
    {
        $model = ArticleDcDescModel::findOne($id);
        $model->content = $this->post('content');
        $model->type = 0; // 文章
        $model->article_id = 0; // 默认
        if($model->save()) return Common::response(1, '更新成功');
        return Common::response(0, '更新失败', $model->getErrors());
    }

    public function actionDelete($id)
    {
        $model = ArticleDcDescModel::find()->where(['id' => $id, 'type' => 0])->one();
        if($model->delete()) return Common::response(1, '删除成功');
        return Common::response(0, '删除失败', $model->getErrors());
    }


    //  ----------------------------  //


    /*
     * 指定文章
     * */
    public function actionSaveCertain(){
        $model = ArticleDcDescModel::find()->where(['type' => 1])->one();
        $model->article_id = $this->post('id') ?? 1;
        if($model->save()) return Common::response(1, '更新成功', ['id' => $model->article_id]);
        return Common::response(0, '更新失败', $model->getErrors());
    }

    /*
     * 指定贷超详情的标题
     * */
    public function actionSaveTitle(){
        $model = ArticleDcDescModel::find()->where(['type' => 2])->one();
        $model->content = $this->post('title') ?? '点我查看最有效的推广方式';
        if($model->save()) return Common::response(1, '更新成功', ['title' => $model->content]);
        return Common::response(0, '更新失败', $model->getErrors());
    }

    /*
     * 获取文章id和标题
     * */
    public function actionDcDetailInfo(){
        $title =  ArticleDcDescModel::find()->select('content as title')->where(['type' => 2])->scalar();
        $article_id =  ArticleDcDescModel::find()->select('article_id')->where(['type' => 1])->scalar();
        return [
            'items' => [
                [
                    'id' => $article_id,
                    'title' => $title
                ]
            ]
        ];
    }

    // -------------------------------------  //
    // 报备流程

    /*
     * 获取报备文章id和标题
     * */
    public function actionReportDetailInfo(){
        $title =  ArticleDcDescModel::find()->select('content as title')->where(['type' => 4])->scalar();
        $article_id =  ArticleDcDescModel::find()->select('article_id')->where(['type' => 3])->scalar();
        return [
            'items' => [
                [
                    'id' => $article_id,
                    'title' => $title
                ]
            ]
        ];
    }

    /*
     * 指定报备文章
     * */
    public function actionSaveReportArticle(){
        $model = ArticleDcDescModel::find()->where(['type' => 3])->one();
        $model->article_id = $this->post('id') ?? 1;
        if($model->save()) return Common::response(1, '更新成功', ['id' => $model->article_id]);
        return Common::response(0, '更新失败', $model->getErrors());
    }

    /*
     * 指定报备详情的标题
     * */
    public function actionSaveReportTitle(){
        $model = ArticleDcDescModel::find()->where(['type' => 4])->one();
        $model->content = $this->post('title') ?? '点我查看最有效的推广方式';
        if($model->save()) return Common::response(1, '更新成功', ['title' => $model->content]);
        return Common::response(0, '更新失败', $model->getErrors());
    }

}
