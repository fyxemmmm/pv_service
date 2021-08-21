<?php

namespace api\controllers;
use common\models\Common;
use common\models\SuggestionModel;
use common\models\SuggestionPicModel;
use Yii;

// 建议和反馈
class SuggestionController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionCreate(){
        // 差图片
        $queryParams = Yii::$app->request->post();
        $queryParams['user_id'] = $this->userId;
        $queryParams['create_time'] = date('Y-m-d H:i:s');
        $model = new SuggestionModel();
        $model->setAttributes($queryParams);
        if($model->save()){
            if(isset($queryParams['pics']) && !empty($queryParams['pics'])){
                $id = $model->id;
                foreach ($queryParams['pics'] as $k=>$v){
                    $suggestion_pic_model = new SuggestionPicModel();
                    $suggestion_pic_model->suggestion_id = $id;
                    $suggestion_pic_model->img_url = $v;
                    $suggestion_pic_model->save();
                }
            }
            return common::response(1, '操作成功', $model);
        }
        return common::response(1, '操作失败',$model->getErrors());
    }

}
