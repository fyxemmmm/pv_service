<?php

namespace api\controllers;

use common\models\BusinessReportModel;
use common\models\Common;

/**
 * CityController implements the CRUD actions for CityModel model.
 */
class BusinessReportController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionCreate(){
        $model = new BusinessReportModel();
        $post = $this->post();
        $post['user_id'] = $this->userId;
        $post['create_time'] = date('Y-m-d H:i:s');
        $model->attributes = $post;
        if ($model->save()) return Common::response(1, '举报成功');
        return Common::response(0, '操作失败', $model->getErrors());
    }

}