<?php

namespace api\controllers;

use api\models\Article;
use Codeception\Step\Comment;
use common\models\ArticleCommentLikeModel;
use common\models\ArticleCommentModel;
use common\models\UserModel;
use Yii;
use api\models\ArticleComment;
use api\models\CommentSearch;
use yii\web\NotFoundHttpException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;
use common\models\Common;
use api\models\MessageDynamic;
use common\Helper;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class CommentController extends CommonController
{
    public $modelClass = '';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index','view']
        ];
        return $behaviors;
    }


    public function actions()
    {
        ArticleComment::$userId = $this->userId;
        CommentSearch::$userId = $this->userId;
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index']);
        return $actions;
    }

    /**
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        ArticleComment::$switch = 1;
        $searchModel = new CommentSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['CommentSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionCommentDetail()
    {
        $searchModel = new CommentSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['CommentSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }


    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $status = UserModel::find()->where(['id' => $this->userId])->select('status')->scalar();
        $pravite = $status == 2 ? 1 : 0; // 用户状态为2 那么发布的评论是仅自己可见
        $del = $status == 0 ? 1 : 0; // 用户状态为0 那么发布的评论是已删除的状态
        $model = new ArticleComment();
        $pid = $this->getTopPid($this->post('pid'));
        $relpy_pid = $this->post('pid');
        $article_id = $this->post('id');
        $content = $this->post('content');
        $author_model = Article::find()->where(['id'=> $article_id])->one();
        $author_id = $author_model ? $author_model->creater : 0;

        $values = [
            'article_id' => $article_id,
            'content' => $content,
            'create_time' => date('Y-m-d H:i:s'),
            'user_id' => $this->userId,
            'pid' => $pid,
            'reply_pid' => $relpy_pid,
            'is_private' => $pravite,
            'del' => $del
        ];

        $model->attributes = $values;
        $model->save();

        $comment_id = Yii::$app->db->getLastInsertID(); // 获得返回
        //
        $user_model = UserModel::find()->where(['id' => $this->userId])->one();

        $data = [
            'id' => $comment_id,
            'user_id' => $this->userId,
            'content' => $content,
            'create_time' => date('Y-m-d H:i:s'),
            'nick_name' => $user_model['nick_name'] ?? '',
            'avatar_image' => $user_model['avatar_image'] ?? '',
            'time_before' => '刚刚'
        ];

        // start
        $dynamic_model = new MessageDynamic();  // 动态表

        if($pid != 0){  // 回复评论
            $model = new ArticleComment();
            $model::findOne($pid)->updateCounters(['child_count' => 1]); // 用于热门评论排序
            // 回复评论 动态表
            $accept_user = ArticleComment::find()->where(['id' => $relpy_pid])->one();
            $accept_user_id = $accept_user ? $accept_user->user_id : 0;
            $dynamic_data = [
                'article_comment_id' => $comment_id,   //  xxxx 评论了 xx 中的 xx
                'type' => MessageDynamic::$comment,
                'content' => $content,
                'user_id' => $this->userId,
                'accept_user_id' => $accept_user_id,
                'create_time' => date('Y-m-d H:i:s')
            ];
            // 友盟推送 start
            $umeng_arr = $dynamic_data;
            $umeng_arr['comment_id'] = $relpy_pid;   //  xxxx 评论了 xx 中的xxxx
            $umeng_arr['comment_id_after'] = $comment_id;   //  xxxx 评论了 xx 中的xx
            Helper::pushMessage(2, $umeng_arr);
            // 推送 end
        }

        if($pid == 0){
            // 评论文章   动态表
            $dynamic_data = [
                'article_comment_id' => $comment_id,   //  评论文章的评论id
                'type' => MessageDynamic::$article,
                'content' => $content,
                'user_id' => $this->userId,
                'accept_user_id' => $author_id,
                'create_time' => date('Y-m-d H:i:s')
            ];
            // 友盟推送 start
            try{
                $umeng_arr = $dynamic_data;
                $umeng_arr['article_id'] = $article_id;
                Helper::pushMessage(1,$umeng_arr);
            }catch (\Exception $e){
                // pass
            }
            // end
        }
        $dynamic_model->attributes = $dynamic_data;
        $dynamic_model->save();
        //end

        // 文章评论数 + 1
        Article::findOne($article_id)->updateCounters(['comment_num' => 1]);

        return common::response(1, '添加成功',$data);
    }



    // pid为0时 截停程序
    public function getTopPid($pid){
        if($pid != 0){
            $data = ArticleCommentModel::find()->where(['id'=>$pid])->asArray()->one();
            if(!$data) return 0;
            if($data['pid'] == 0){
                return $pid;
            }else{
                return $data['pid'];
            }
        }
        return 0;
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
        $user_id = $this->userId;
        $model = ArticleCommentLikeModel::find()->where(['comment_id' => $id, 'user_id' => $user_id])->one();
        $active = 0;
        if($model){
            $model->delete();
            ArticleComment::findOne($id)->updateCounters(['like_num' => -1]);  // 评论点赞数加一

        }else{
            $model = new ArticleCommentLikeModel();
            $model->user_id = $user_id;
            $model->comment_id = $id;
            $model->save();
            $active = 1;
            ArticleComment::findOne($id)->updateCounters(['like_num' => 1]);
        }
        return common::response(1, '添加成功', ['active' => $active]);
    }

    /**
     * Deletes an existing Article model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = new ArticleComment();
        $model = $model::find()->where(['id' => $id, 'user_id' => $this->userId])->one();
        if($model){
            $model->delete();
            return common::response(0, '删除成功');
        }
        return common::response(0, '删除失败');
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
        if (($model = Comment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
