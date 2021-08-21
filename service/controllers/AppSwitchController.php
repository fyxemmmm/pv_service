<?php

namespace service\controllers;
use common\models\Common;

class AppSwitchController extends CommonController
{
    public $modelClass = 'common\models\AppSwitchModel';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index']);
        return $actions;
    }

    public function actionGetKey()
    {
        $key = $this->get('key');
        $value = Common::getAppSwitchKey($key);
        return $value;
    }

    public function actionSetKey()
    {
        $key = $this->post('key');
        $value = $this->post('value');
        
        Common::setAppSwitchKey($key, $value);
        return Common::messageReturn(1, 'Success');
    }

    public function actionGetSerializeKey()
    {
        $key = $this->get('key');
        $value = Common::getAppSwitchKey($key);
        return $value ? unserialize($value) : [];
    }

    public function actionSetSerializeKey()
    {
        $post = $this->post();
        $key = $post['key'];
        unset($post['key']);

        $value = serialize($post);
        Common::setAppSwitchKey($key, $value);
        return Common::messageReturn(1, 'Success');
    }

}
