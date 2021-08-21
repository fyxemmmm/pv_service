<?php

namespace api\controllers;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\UserCardModel;
use common\Helper;
use common\models\Common;


class UserCardController extends CommonController
{
    public $modelClass = '';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /*
     * 卡  支付宝、银行卡
     * */
    public function actionIndex()
    {
        $model = UserCardModel::find()->where(['user_id' => $this->userId]);
        $data = Helper::usePage($model);
        foreach ($data['items'] as $k => &$v){
            switch ($v['type']){
                case 0:  // 支付宝
                    unset($v['yhk_mobile']);
                    unset($v['yhk_number']);
                    unset($v['yhk_name']);
                    break;
                case 1:  // 银行卡
                    unset($v['zfb_account']);
                    unset($v['zfb_receive_name']);
                    unset($v['zfb_receive_mobile']);
                    break;
            }
        }
        return  $data;
    }


    public function actionCreate(){
        $type = (string)$this->post('type');
        if($type !== "0" && $type !== "1") return Common::response(0, '请传递正确的类型信息');
        $post = $this->post();
        $model = new UserCardModel();
        $post['user_id'] = $this->userId;
        $post['create_time'] = date('Y-m-d H:i:s');
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, '信息添加成功');
        return Common::response(0, '信息添加失败', $model->getErrors());
    }


    public function actionDelete($id){
        $model = UserCardModel::find()->where(['id' => $id, 'user_id' => $this->userId])->one();
        if($model){
            if($model->delete())  return Common::response(1, '删除成功');
            return Common::response(0, '信息添加失败', $model->getErrors());
        }
        return Common::response(0, '未找到相关卡的信息');
    }



}
