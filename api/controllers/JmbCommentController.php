<?php

namespace api\controllers;
use common\models\JmbCommentLikeModel;
use common\models\JmbCommentModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\JmbComment;
use yii\data\ActiveDataProvider;
use common\models\UserModel;
use common\models\Common;


class JmbCommentController extends CommonController
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
            'except' => ['index', 'view']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        JmbComment::$userId = $this->userId;
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex(){
        $query = JmbComment::find();
        $jmb_id = $this->get('jmb_id') ?? '';
        if(!$jmb_id) return Common::response(0, '查询未知的id');
        $query = $query->where(['jmb_id' => $jmb_id, 'pid'=>0])->orderBy('id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }


    public function actionView($id){
        $query = JmbComment::find()->where(['id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }


    public function actionCreate(){
//        var_dump($this->userId);exit();
        $model = new JmbComment();
        $pid = $this->getTopPid($this->post('pid'));
        $relpy_pid = $this->post('pid');
        $jmb_id = $this->post('id');
        $content = $this->post('content');
        $user_model = UserModel::find()->where(['id' => $this->userId])->one();
        $data = [
            'jmb_id' => $jmb_id,
            'content' => $content,
            'create_time' => date('Y-m-d H:i:s'),
            'user_id' => $this->userId,
            'pid' => $pid,
            'reply_pid' => $relpy_pid,
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

        return Common::response(1, '添加成功',$data);
    }

    public function getTopPid($pid){
        if($pid != 0){
            $data = JmbComment::find()->where(['id'=>$pid])->asArray()->one();
            if(!$data) return 0;
            if($data['pid'] == 0){
                return $pid;
            }else{
                return $data['pid'];
            }
        }
        return 0;
    }


    // 评论的点赞toggle
    public function actionClickLike(){
        $id = $this->post('id');
        $model = JmbCommentLikeModel::find()->where(['comment_id' => $id, 'user_id' => $this->userId])->one();
        $active = 0;
        if($model){
            $model->delete();
            JmbCommentModel::findOne($id)->updateCounters(['like_num' => -1]);  // 评论点赞数减一
        }else{
            $model = new JmbCommentLikeModel();
            $model->user_id =  $this->userId;
            $model->comment_id = $id;
            $model->save();
            $active = 1;
            JmbCommentModel::findOne($id)->updateCounters(['like_num' => 1]);
        }
        return Common::response(1, '添加成功', ['active' => $active]);
    }



}
