<?php

namespace api\controllers;

use yii\data\ActiveDataProvider;
use api\models\Radio;
use common\models\RadioCollectModel;
use Yii;

class RadioCollectController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    // 电台收藏
    public function actionCollect(){
        $radio_id = $this->post('id');
        $model = RadioCollectModel::find()->where(['radio_id' => $radio_id,"user_id" => $this->userId])->one();
        $active = 0;
        if($model){
            $model->delete();
            Radio::findOne($radio_id)->updateCounters(['like_num' => -1]);
        }else{
            $model = new RadioCollectModel();
            $model->user_id = $this->userId;
            $model->radio_id = $radio_id;
            $model->save();
            $active = 1;
            Radio::findOne($radio_id)->updateCounters(['like_num' => 1]);
        }
        $res = ['active'=>$active, 'radio_id'=>$radio_id];
        return $res;
    }

}

