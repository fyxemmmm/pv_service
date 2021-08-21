<?php

namespace api\controllers;

use common\models\RadioCommentLikeModel;
use common\models\RadioCommentModel;
use common\models\Common;

class RadioCommentLikeController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    // 评论的点赞toggle
    public function actionUpdate($id){
        $model = RadioCommentLikeModel::find()->where(['comment_id' => $id, 'user_id' => $this->userId])->one();
        $active = 0;
        if($model){
            $model->delete();
            RadioCommentModel::findOne($id)->updateCounters(['like_num' => -1]);  // 评论点赞数减一
        }else{
            $model = new RadioCommentLikeModel();
            $model->user_id =  $this->userId;
            $model->comment_id = $id;
            $model->save();
            $active = 1;
            RadioCommentModel::findOne($id)->updateCounters(['like_num' => 1]);
        }
        return Common::response(1, '添加成功', ['active' => $active]);
    }

}