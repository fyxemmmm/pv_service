<?php

namespace api\controllers;

use Yii;
use api\models\ArticleCollect;
use api\models\ArticleCollectSearch;
use yii\web\NotFoundHttpException;
use common\models\CollectionsModel;
use common\Helper;
use common\models\ArticleModel;
use common\models\JmbModel;


/**
 * ArticleCollectController implements the CRUD actions for ArticleCollectModel model.
 */
class ArticleCollectController extends CommonController
{

    public $modelClass = 'api\models\ArticleCollect';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update'], $actions['create']);
        return $actions;
    }

    /**
     * Lists all ArticleCollectModel models.
     * @return mixed
     */
    public function actionIndex2()
    {
        $searchModel = new ArticleCollectSearch();
        $search['ArticleCollectSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionIndex()
    {
        $user_id = $this->get('user_id');
        $model = CollectionsModel::find()->where(['user_id' => $user_id])->orderBy('id desc');
        $data = Helper::usePage($model);
        foreach ($data['items'] as $k=>&$v){
            if($v['type'] == "0"){  // 文章收藏
                $info =  ArticleModel::find()->select('title,preview_image,comment_num,like_num')->where(['id' => $v['item_id']])->asArray()->one();
                $v['title'] = $info['title'];
                $v['preview_image'] = $info['preview_image'];
                $v['comment_num'] = $info['comment_num'];
                $v['like_num'] = $info['like_num'];
                $v['article_id'] = $v['item_id'];
            }elseif ($v['type'] == "1"){ // 收藏加盟宝
                $jmb_info = JmbModel::find()->select('name as title,image_url as preview_image')->where(['id' => $v['item_id']])->asArray()->one();
                $v['title'] = $jmb_info['title'];
                $v['preview_image'] = $jmb_info['preview_image'];
                $v['jmb_id'] = $v['item_id'];
            }
            unset($v['item_id']);
        }
        Helper::formatData($data);
        return $data;
    }



    /**
     * Creates a new ArticleCollectModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ArticleCollect();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ArticleCollectModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ArticleCollectModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ArticleCollectModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ArticleCollectModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ArticleCollectModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
