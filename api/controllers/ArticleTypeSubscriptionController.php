<?php

namespace api\controllers;

use common\models\Common;
use common\models\ArticleTypeSubscriptionModel as Model;
use common\models\ArticleTypeModel;

class ArticleTypeSubscriptionController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    // 订阅和取消订阅
    public function actionToggle(){
        $article_type_id = $this->post('article_type_id', 0);
        $data = Model::find()->where(['user_id' => $this->userId])->andWhere(['article_type_id' => $article_type_id])->one();
        $is_sub = 0;
        if(!$data){
            $model = new Model();
            $model->user_id = $this->userId;
            $model->article_type_id = $article_type_id;
            $model->save();
            ArticleTypeModel::findOne($article_type_id)->updateCounters(['subscription_num' => 1]);
            $is_sub = 1;
        }else{
            $data->delete();
            ArticleTypeModel::findOne($article_type_id)->updateCounters(['subscription_num' => -1]);
        }
        // 是否订阅，1代表切换成了订阅状态 0代表切换成了未订阅状态
        return Common::response(1, '切换成功',['is_sub'=> $is_sub]);
    }

}