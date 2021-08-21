<?php

namespace api\controllers;

use yii\data\ActiveDataProvider;
use common\models\Common;
use common\models\MessageSettingModel;

class MessageSettingController extends CommonController
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
        $model = MessageSettingModel::find()->where(['user_id' => $this->userId])->one();
        if(!$model){
            $model = new MessageSettingModel();
            $model->user_id = $this->userId;
            $model->save();
        }
        $response = [
            'comment_reply' => $model->comment_reply ?? 0,
            'focus' => $model->focus ?? 0,
            'praise' => $model->praise ?? 0
        ];
        return Common::response(1, '操作成功', $response);
    }


    // 切换接收提醒的状态
    public function actionToggle(){
        $type = $this->get('type');
        $status = $this->get('status') == 1 ? 1 : 0;  // 0 是任何人，1 是从不提醒
        $model = MessageSettingModel::find()->where(['user_id' => $this->userId])->one();
        if(1 == $type){ // 评论或者回复了你
            $model->comment_reply = $status;
        }else if (2 == $type){  // 关注了你
            $model->focus = $status;
        }

        if($model->save()) return Common::response(1, '操作成功', $model);
        return Common::response(0, '操作失败');
    }

}
