<?php

namespace api\controllers;

use common\Helper;
use common\models\InformModel;
use api\models\InformDynamic;
use common\models\Common;
use common\models\UserModel;

class InformDynamicController extends CommonController
{
    public $modelClass = '';


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    /*
     * 贷超、电台通知列表  已废弃 只为兼容以前版本
     * */
    public function actionIndex()
    {
        $user_register_time = UserModel::find()->select('register_time')->where(['id' => $this->userId])->scalar();
        $model = InformModel::find()->where(['>','create_time',$user_register_time]);
        $response = Helper::usePage($model);
        if(!empty($response['items'])){
            foreach($response['items'] as $k=>&$v){
                $v['id'] = intval($v['id']);
                /*
                 * 兼容start
                 * 这里的dc_id的含义已经不仅仅是贷超id了，而是根据type的变化含义而变化
                 * 让app 先判断 type是否为2，是2就跳电台详情,否则就走之前的逻辑
                 * */
                if($v['type'] == 2){   // 为了向后兼容
                    $v['dc_id'] = $v['radio_id'];
                }
                unset($v['radio_id']);
                /*
                 * 兼容end
                 * */

                // 查询InformDynamic表中是否有数据，有代表已读过
                $info = InformDynamic::find()->where(['user_id' => $this->userId, 'inform_id'=> $v['id']])->one();
                $v['create_time'] = Helper::transferTime($v['create_time']);
                if($info){  // 查到有 说明已读
                    $v['read'] = 1;
                }else{  // 没查到,那么就插入表里,代表曾经来过
                    $v['read'] = 0;
                    $model = new InformDynamic();
                    $model->user_id = (int)$this->userId;
                    $model->inform_id = (int)$v['id'];
                    $model->save();
                }
            }
        }

        return Common::response(1, '操作成功', $response);
    }
}
