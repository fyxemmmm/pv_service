<?php

namespace api\controllers;

use yii\data\ActiveDataProvider;
use api\models\Radio;
use common\models\RadioLikeModel;
use Yii;

class RadioLikeController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    // 电台点赞
    public function actionLike(){
        $radio_id = $this->post('id');
        $model = RadioLikeModel::find()->where(['radio_id' => $radio_id,"user_id" => $this->userId])->one();
        $active = 0;
        if($model){
            $model->delete();
            Radio::findOne($radio_id)->updateCounters(['like_num' => -1]);
        }else{
            $model = new RadioLikeModel();
            $model->user_id = $this->userId;
            $model->radio_id = $radio_id;
            $model->save();
            $active = 1;
            Radio::findOne($radio_id)->updateCounters(['like_num' => 1]);
        }
        $res = ['active'=>$active];
        return $res;
    }

}

