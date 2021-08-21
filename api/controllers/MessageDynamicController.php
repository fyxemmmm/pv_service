<?php

namespace api\controllers;

use api\models\MessageDynamic;
use Yii;
use common\models\MessageDynamicModel;
use common\models\MessageDynamicModelSearch;
use yii\web\NotFoundHttpException;
use common\models\ArticleModel;
use common\models\ArticleCommentModel;
use common\models\FocusModel;
use common\models\Common;
use common\models\InformModel;
use common\models\InformDynamicModel;
use common\models\UserModel;


/**
 * MessageDynamicController implements the CRUD actions for MessageDynamicModel model.
 */
class MessageDynamicController extends CommonController
{
    public $modelClass = '';
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionGetDynamic(){
        $page = (int)($this->get('page') ?? 1);
        $per_page = (int)($this->get('per-page') ?? 20);
        if($per_page === 0) $per_page = 1;
        $fields = 'user_id,type,create_time,content,article_comment_id,user.avatar_image,user.nick_name,read';
        $model = MessageDynamicModel::find()->select($fields)
                                           ->join('LEFT JOIN','user','user.id=message_dynamic.user_id')
                                           ->where(['message_dynamic.accept_user_id' => $this->userId])
//                                           ->andWhere(['message_dynamic.read' => 0])
                                           ->orderBy('message_dynamic.id desc');

        $totalCount = (int)$model->count();
        $page_count = ceil($totalCount / $per_page);
        if($per_page * $page > $totalCount){
            $page = $page_count;
        }

        $data = $model->offset($page-1)
            ->limit($per_page)
            ->asArray()
            ->all();

        foreach($data as $k=>&$v){
            if(!empty($v['create_time'])){
                $now_diff = time() - strtotime($v['create_time']);
                if($now_diff / 86400 > 1){
                    $day = $now_diff / 86400;
                    $v['create_time'] = floor($day).'天前';
                }elseif ($now_diff / 3600 > 1){
                    $hour = $now_diff / 3600;
                    $v['create_time'] = floor($hour).'小时前';
                }elseif ($now_diff / 60 > 1){
                    $minute = $now_diff / 60;
                    $v['create_time'] = floor($minute).'分钟前';
                }else{
                    $v['create_time'] = '刚刚';
                }
            }else{
                $v['create_time'] = '';
            }

            // 文章类型
            if($v['type'] == MessageDynamic::$article){
                $article_id = ArticleCommentModel::find()->select('article_id')->where(['id'=>$v['article_comment_id']])->scalar();
                $info = ArticleModel::find()->where(['id' => $article_id])->asArray()->one();
                $v['info'] = $info ? $info['title'] : '';
                $v['article_image'] = $info ? $info['preview_image'] : '';
                $v['article_id'] = $info ? $info['id'] : 0;
                $v['top_comment_id'] = $v['article_comment_id']; // 顶级评论
                // 评论类型
            }else if ($v['type'] == MessageDynamic::$comment){
                $info = ArticleCommentModel::find()->where(['id' => $v['article_comment_id']])->asArray()->one();
                $info = ArticleCommentModel::find()->where(['id' => $info['pid']])->asArray()->one();
                $top_comment_id = $this->getTopCommentId($info['id']);
                $v['info'] = $info ? $info['content'] : '';
                $v['article_id'] = $info ? $info['article_id'] : 0;
                $article_info = ArticleModel::find()->where(['id' => $v['article_id']])->asArray()->one();
                $v['article_image'] = $article_info ? $article_info['preview_image'] : '';
                $v['top_comment_id'] = $top_comment_id; // 顶级评论
            // 异常
            }else{
                $v['info'] = '';
                $v['article_image'] = '';
                $v['article_id'] = '';
                $v['top_comment_id'] = '';
            }
        }
        MessageDynamicModel::updateAll(['read' => 1], ['read' => 0,'accept_user_id' => $this->userId]);

        $response = [
            'items' => $data,
            '_meta' => [
                'totalCount'=>$totalCount,'pageCount'=>$page_count,'currentPage'=>$page,'perPage'=>$per_page
            ]

        ];

        return Common::response(1, '操作成功', $response);
    }


    // 获取最高级别的评论id
    public function getTopCommentId($id){
        $data = ArticleCommentModel::find()->where(['id'=>$id])->asArray()->one();
        if($data['pid'] == '0') return $data['id'];
        return $this->getTopCommentId($data['id']);
    }



    // 获取新动态的总个数
    public function actionNewDynamicNum(){
        // 贷超广播的数量列表
        $user_register_time = UserModel::find()->select('register_time')->where(['id' => $this->userId])->scalar();
        $ids = InformModel::find()->select('id')->where(['>','create_time',$user_register_time])->asArray()->all();//->asArray()->all();
        $ids = array_column($ids, 'id');
        $count_total = count($ids); // 总数
        $count_read = InformDynamicModel::find()->where(['user_id' => $this->userId])->andWhere(['in','inform_id',$ids])->count(); // 多少条已经读的
        $inform_num = $count_total - $count_read;

        // 文章 评论的动态列表
        $dynamic_num = (int)MessageDynamicModel::find()->where(['accept_user_id' => $this->userId])->andWhere(['read' => 0])->count();
        // 粉丝的动态列表
        $new_fans_num = (int)FocusModel::find()->where(['focus_user_id' => $this->userId])->andWhere(['read' => 0])->count();

        $res = [
            'inform_num' => $inform_num,
            'dynamic_num' => $dynamic_num,
            'new_fans_num' => $new_fans_num,
        ];

        return Common::response(1, '操作成功', $res);
    }

    /**
     * Lists all MessageDynamicModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MessageDynamicModelSearch();
        $search['MessageDynamicModelSearch'] = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    /**
     * Displays a single MessageDynamicModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MessageDynamicModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MessageDynamicModel();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing MessageDynamicModel model.
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
     * Deletes an existing MessageDynamicModel model.
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
     * Finds the MessageDynamicModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MessageDynamicModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MessageDynamicModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
