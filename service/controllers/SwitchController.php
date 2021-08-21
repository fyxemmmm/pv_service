<?php

namespace service\controllers;

use common\models\SwitchModel;
use common\models\Common;
use common\Helper;



class SwitchController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        $model = SwitchModel::find();
        $data = Helper::usePage($model);
        foreach ($data['items'] as $k=>&$v){
            $v['status'] = (int)$v['status'];
            $v['id'] = (int)$v['id'];
            if($v['status'] == 1){
                $v['content'] = '安卓贷超已开启';
            }else if ($v['status'] == 2){
                $v['content'] = 'ios贷超已经开启';
            }else if ($v['status'] == 3){
                $v['content'] = '全部贷超已经开启';
            }else{
                $v['content'] = '开启全部文章';
            }
            $v['name'] = '贷超';
        }
        return $data;
    }


    // 切换
    public function actionToggle(){
        $id = $this->post('id');
        $type = $this->post('type');
        switch ($type){
            case 1:
                $device = '安卓';
                break;
            case 2:
                $device = 'ios';
                break;
            case 3:
                $device = '全部代超';
                break;
            case 4:
                $device = '全部文章';
                break;
        }
        $model = SwitchModel::find()->where(['id' => $id])->one();
        $model->status = $type;
        $model->save();
        return Common::response(1, "{$device}开启贷超");
    }

}
