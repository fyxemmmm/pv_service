<?php

namespace service\controllers;

use yii\data\ActiveDataProvider;
use common\models\Common;
use common\models\InformDcModel;
use common\Helper;

class InformDcController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        $query = InformDcModel::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    /*
     * 贷超系统通知  广播所有用户
     * */
    public function actionCreate(){
        $post = $this->post();
        $post['create_time'] = date('Y-m-d H:i:s');
        $model = new InformDcModel();
        $model->setAttributes($post);
        if($model->save()){
            Helper::pushMessage(7,$post);
            return Common::response(1, '操作成功', $model);
        }
        return Common::response(0, '操作失败', $model->getErrors());
    }

}
