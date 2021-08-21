<?php

namespace api\controllers;

use common\models\Common;
use common\models\RadioReportModel;


class RadioReportController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionCreate()
    {
        $radio_id = $this->post('id');
        $content = $this->post('content') ?? '';
        $model = new RadioReportModel();
        $data = [
            'user_id' => $this->userId,
            'radio_id' => $radio_id,
            'content' => $content,
            'create_time' => date('Y-m-d H:i:s')
        ];
        $model->attributes = $data;
        $model->save();
        return Common::response(1,  '操作成功');
    }

}
