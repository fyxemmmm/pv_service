<?php

namespace api\controllers;

use common\models\ArticleModel;
use common\models\ArticleCollectModel;
use common\models\UserModel;
use Yii;
use api\models\Article;
use api\models\ArticleSearch;
use yii\web\NotFoundHttpException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;
use common\models\ArticleLikeModel;
use common\models\Common;
use common\models\FocusModel;
use common\models\ArticleBrowseModel;
use common\Helper;
use common\models\CollectionsModel;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends CommonController
{
    public $modelClass = 'api\models\Article';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index','view','get-info']
        ];
        return $behaviors;
    }


    public function actions()
    {
        Article::$userID = $this->userId;
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }



    /**
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->recordUserBrowse($this->get('id'));
        $searchModel = new ArticleSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['ArticleSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    public function actionGetInfo(){
        $this->recordUserBrowse($this->get('id'));
        $searchModel = new ArticleSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['ArticleSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    // 记录用户的浏览记录
    public function recordUserBrowse($id){
        if($this->userId && $id){
            $model = new ArticleBrowseModel();
            $model->article_id = $id;
            $model->user_id = $this->userId;
            $model->create_time = date('Y-m-d H:i:s');
            $model->save();
        }
        return true;
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Article();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $queryParams = Yii::$app->request->queryParams;
        $type = (int)$queryParams['type'] ?? 1;
        $article_id = (int)$queryParams['id'] ?? '';
        $active = 0;
        if(!$article_id){
            return Common::response(0, '未找到文章信息');
        }
        if($type == Article::$like){
            $model = ArticleLikeModel::find()->where(['article_id' => $article_id,"user_id" => $this->userId])->one();
            if($model){
                $model->delete();
                Article::findOne($article_id)->updateCounters(['like_num' => -1]);
            }else{
                $model = new ArticleLikeModel();
                $model->user_id = $this->userId;
                $model->article_id = $article_id;
                $model->save();
                $active = 1;
                Article::findOne($article_id)->updateCounters(['like_num' => 1]);
            }
        }

        if($type == Article::$collect){  // 文章收藏
//            $model = ArticleCollectModel::find()->where(['article_id' => $article_id,"user_id" => $this->userId])->one();
            $model = CollectionsModel::find()->where(['item_id' => $article_id,"type"=>0,"user_id" => $this->userId])->one();
            if($model){
                $model->delete();
            }else{
                $model = new CollectionsModel();
                $model->user_id = $this->userId;
                $model->item_id = $article_id;
                $model->type = 0;
                $model->save();
                $active = 1;
            }
        }

        if($type == Article::$focus){
            $model = ArticleModel::find()->where(['id' => $article_id])->one();
            $author_id = $model ? $model->creater : 0;
            if($author_id){
                $model = FocusModel::find()->where(['focus_user_id' => $author_id])->andWhere(['user_id'=>$this->userId])->one();
                if($model){
                    // 取消关注
                    UserModel::findOne($this->userId)->updateCounters(['focus_num' => -1]);
                    UserModel::findOne($author_id)->updateCounters(['focused_num' => -1]);
                    $model->delete();
                }else{
                    // 关注
                    UserModel::findOne($this->userId)->updateCounters(['focus_num' => 1]);
                    UserModel::findOne($author_id)->updateCounters(['focused_num' => 1]);

                    $model = new FocusModel();
                    $model->user_id = $this->userId;
                    $model->focus_user_id = $author_id;
                    $model->save();
                    $active = 1;
                    Helper::pushMessage(4,['user_id'=>$this->userId, 'accept_user_id'=>$author_id]);
                }
            }
        }
        $res = ['active'=>$active];
        if($type == Article::$collect) $res['article_id'] = $article_id;

        return Common::response(1, '切换成功', $res);
    }


    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Article the loaded model
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
