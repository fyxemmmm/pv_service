<?php

namespace service\controllers;

use common\models\ArticleLabelModel;
use common\models\LabelModel;
use Yii;
use common\models\ArticleModel;
use common\models\ArticleModelSearch;
use service\models\Article;
use service\models\ArticleSearch;
use service\controllers\CommonController;
use common\models\Common;

/**
 * ArticlelController implements the CRUD actions for ArticleModel model.
 */
class ArticleController extends CommonController
{
    public $modelClass = 'service\models\Article';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    /**
     * Lists all ArticleModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArticleSearch();
        $search['ArticleSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    public function actionView()
    {
        $searchModel = new ArticleSearch();
        $search['ArticleSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    /**
     * Creates a new ArticleModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Article();
        $content = htmlspecialchars($this->post('content'));
        $post = Yii::$app->request->post();

        // 给默认的文章喜欢数
        $rand = rand(0, 50);
        $post['like_num'] = $rand;

        $label_name_arr = $post['label_id_list'] ?? [];  // 传递过来的是label的name
        unset($post['label_id_list']);

        $model->setAttributes($post);
        $model->content = $content;
        $model->create_time = Common::generateDatetime();

        if ($model->save()) {
            foreach ($label_name_arr as $name) {
                $label = LabelModel::find()->where(['name' => $name])->one();
                $article_label_model = new ArticleLabelModel();
                if($label){
                    $article_label_model->setAttributes(['article_id' => $model->id, 'label_id' => $label->id ?: 0]);
                }else{
                    $label_model = new LabelModel();
                    $label_model->name = $name;
                    $label_model->save();
                    $label_id = Yii::$app->db->getLastInsertID(); // 获得返回id
                    $article_label_model->setAttributes(['article_id' => $model->id, 'label_id' => $label_id]);
                }
                $article_label_model->save();
            }
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Updates an existing ArticleModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        if(isset($post['status'])){
            $model->setAttributes($post);
            $model->save();
            return Common::response(1, 'success', $model);
        }

        // 1,2,3,4
        $label_name_arr = $post['label_id_list'] ?? [];  // 传递过来的是label的name
        unset($post['label_id_list']);
        ArticleLabelModel::deleteAll(['article_id' => $id]);
        foreach ($label_name_arr as $name) {
            $label = LabelModel::find()->where(['name' => $name])->one();
            $article_label_model = new ArticleLabelModel();
            if($label){
                $article_label_model->setAttributes(['article_id' => $id, 'label_id' => $label->id ?: 0]);
            }else{
                $label_model = new LabelModel();
                $label_model->name = $name;
                $label_model->save();
                $label_id = Yii::$app->db->getLastInsertID(); // 获得返回id
                $article_label_model->setAttributes(['article_id' => $id, 'label_id' => $label_id]);
            }
            $article_label_model->save();
        }

        $model->setAttributes($post);
        if (null !== $this->post('content')) {
            $model->content = htmlspecialchars($this->post('content'));
        }
        $model->update_time = Common::generateDatetime();
        if ($model->save()) {
            return Common::response(1, 'success', $model);
        }
        return Common::response(0, 'failure', $model->getErrors());
    }

    /**
     * Deletes an existing ArticleModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $res = ArticleModel::updateAll(['status' => 0], ['id' => $id]);
        if ($res) return Common::response(1, '删除成功');
        return Common::response(0, '删除失败');

    }

    /**
     * Finds the ArticleModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ArticleModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
