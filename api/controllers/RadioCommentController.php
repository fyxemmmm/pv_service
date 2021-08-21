<?php

namespace api\controllers;

use api\models\Radio;
use api\models\RadioComment;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\RadioCommentSearch;
use common\models\UserModel;
use common\models\Common;
use Yii;

// 电台评论
class RadioCommentController extends CommonController
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
        RadioComment::$userId = $this->userId;
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        $searchModel = new RadioCommentSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['RadioCommentSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionView($id)
    {
        $searchModel = new RadioCommentSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['RadioCommentSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    /*
     * 评论电台 / 回复评论
     * */
    public function actionCreate(){
        $status = UserModel::find()->where(['id' => $this->userId])->select('status')->scalar();
        $pravite = $status == 2 ? 1 : 0; // 用户状态为2 那么发布的评论是仅自己可见
        $del = $status == 0 ? 1 : 0; // 用户状态为0 那么发布的评论是已删除的状态
        $model = new RadioComment();
        $pid = $this->getTopPid($this->post('pid'));
        $relpy_pid = $this->post('pid');
        $radio_id = $this->post('id');
        $content = $this->post('content');
        $user_model = UserModel::find()->where(['id' => $this->userId])->one();
        $data = [
            'radio_id' => $radio_id,
            'content' => $content,
            'create_time' => date('Y-m-d H:i:s'),
            'user_id' => $this->userId,
            'pid' => $pid,
            'reply_pid' => $relpy_pid,
            'is_private' => $pravite,
            'del' => $del,
            /*
             * 后续添加不入库 只是用于返回
             * */
            'nick_name' => $user_model['nick_name'] ?? '',
            'avatar_image' => $user_model['avatar_image'] ?? '',
            'time_before' => '刚刚'
        ];

        $model->attributes = $data;
        $model->save();  // 保存到comment表
        $data = array_merge(['id' => $model->id], $data);
        if($pid != 0){  // 评论回复
            $model = new RadioComment();
            $model::findOne($pid)->updateCounters(['child_count' => 1]); // 用于热门评论排序
        }
        Radio::findOne($radio_id)->updateCounters(['comment_num' => 1]);
        return Common::response(1, '添加成功',$data);
    }

    public function getTopPid($pid){
        if($pid != 0){
            $data = RadioComment::find()->where(['id'=>$pid])->asArray()->one();
            if(!$data) return 0;
            if($data['pid'] == 0){
                return $pid;
            }else{
                return $data['pid'];
            }
        }
        return 0;
    }


}



