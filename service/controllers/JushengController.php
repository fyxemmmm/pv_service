<?php

namespace service\controllers;

use common\models\Common;
use common\models\JushengSixImgModel;
use yii\data\ActiveDataProvider;


class JushengController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    // 6大类目图
    public function actionSixCateImgs(){
        $query = JushengSixImgModel::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionEditSixCateImg(){
        $id = $this->post('id');
        $img_url = $this->post('image_url');
        $model = JushengSixImgModel::find()->where(['id' => $id])->one();
        if($model){
            $model->image_url = $img_url;
            $model->save();
        }
        return Common::response(1, 'success', $model);
    }

}
