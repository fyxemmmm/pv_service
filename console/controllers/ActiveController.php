<?php
namespace console\controllers;

use yii\console\Controller;
use common\models\UserModel;
use common\models\Common;

/*
 * 用于激活 10分钟没有激活就发一条短信让其激活
 * */
class ActiveController extends Controller
{
    public $message;

    public function options($actionID)
    {
        return ['message'];
    }

    public function optionAliases()
    {
        return ['m' => 'message'];
    }

    public function actionIndex()
    {
        $this->active_xijin();  // 激活犀金

    }

    public function active_xijin(){
        // 10分钟没有激活就发一条短信 让其激活 前20分钟到前10分钟
        $time_start = date('Y-m-d H:i:s',time()-10*60); // 10分钟
        $time_end = date('Y-m-d H:i:s',time()-20*60); // 20分钟
        $phone_arr = UserModel::find()->select('mobile')
            ->where(['<','register_time',$time_start])
            ->andWhere(['>','register_time',$time_end])
            ->andWhere(['or',['active' => 0],['active' => null]])
            ->asArray()->all();
        $phone_arr = array_column($phone_arr, 'mobile');
        foreach ($phone_arr as $k=>$v){
            Common::send_active_msg($v, 'xijin');
        }
        return true;
    }

}