<?php

namespace api\controllers;

use common\Bridge;
use common\Config;
use common\Helper;
use common\models\Common;
use common\models\UserModel;
use common\models\UserRelationshipModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\UserDcReportModel;

class UserDcReportController extends CommonController
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
            'except' => ['']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $status = $this->get('status');
        $fields = [
            'id',
            'order_sign',
            'name',
            'product_image',
            'pay_money',
            'status',
            'return_money',
            'update_time'
        ];
        $query = UserDcReportModel::find()->select($fields)->andWhere(['leader_id' => $this->userId])->andFilterWhere(['status' => $status])->orderBy('update_time desc');
        $data = Helper::usePage($query);

        Helper::formatData($data,2);
        return $data;
    }

    public function actionView()
    {
        $id = $this->get('id');
        $query = UserDcReportModel::find()->andWhere(['leader_id' => $this->userId])->where(['id' => $id]);
        $data = Helper::usePage($query);
        foreach ($data['items'] as $k=>&$v){
            $v['loan_date'] = date('Y-m-d',strtotime($v['loan_date']));
        }
        Helper::formatData($data,2);
        return $data;
    }

    // 当前执行此action的是上家用户, 去报备下家用户
    public function actionCreate(){
        $post = $this->post();
        $product_id = $post['product_id'] ?? '';
        if(empty($product_id)) return Common::response(0, '请传入产品id');

        $pay_money = $post['pay_money'] ?? 0;
        if(!is_numeric($pay_money) || $pay_money < 0) return Common::response(0, '下款金额填写不正常');

        $bridge = new Bridge();
        try{
            $product_info = $bridge->fetchProductInfo($product_id);
        }catch (\Exception $e){
            return Common::response(0, '网络异常');
        }

        if(isset($product_info['message'])) return Common::response(0, $product_info['message']);
        $type = (int)$product_info['settlement_type']; // 类型

        // 犀金会员默认白银级提升为黄金级别
        $level = UserModel::find()->select('user_level')->where(['id' => $this->userId])->scalar();
        $is_vip = Common::checkVip($this->userId);
        if($is_vip === true && $level == Bridge::SILVER) $level = Bridge::GOLD;

        if($type === Bridge::RETURN_RATE){
            switch ($level){
                case Bridge::SILVER:
                    preg_match('@^(.*)%$@',$product_info['silver_rate'],$matches);
                    $silver_rate = $matches[1];
                    $return_money = ($pay_money * $silver_rate)/100;
                    break;
                case Bridge::GOLD:
                    preg_match('@^(.*)%$@',$product_info['gold_rate'],$matches);
                    $gold_rate = $matches[1];
                    $return_money = ($pay_money * $gold_rate)/100;
                    break;
                case Bridge::DIAMOND:
                    preg_match('@^(.*)%$@',$product_info['diamond_rate'],$matches);
                    $diamond_rate = $matches[1];
                    $return_money = ($pay_money * $diamond_rate)/100;
                    break;
            }
        }
        elseif ($type === Bridge::RETURN_AWARD){ // 按照固定的金额来返现
            switch ($level){
                case Bridge::SILVER:
                    $return_money = $product_info['silver_award'];
                    break;
                case Bridge::GOLD:
                    $return_money = $product_info['gold_award'];
                    break;
                case Bridge::DIAMOND:
                    $return_money = $product_info['diamond_award'];
                    break;
            }
        }

        $now = date('Y-m-d H:i:s');
        $model = new UserDcReportModel();
        $model->setAttributes($post);
        $attr = [
            'leader_id' => $this->userId,
            'order_sign' => Common::genOrderSign(),
            'create_time' => $now,
            'update_time' => $now,
            'status' => Config::REPORT_DOING,
            'return_money' => $return_money,
            'product_image' => $product_info['image']
        ];

        $model->setAttributes($attr);

        if($model->save()) return Common::response(1, '操作成功');
        return Common::response(0, '操作失败', $model->getErrors());
    }

    public function actionUpdate($id){
        $post = $this->post();
        $product_id = $post['product_id'] ?? '';
        if(empty($product_id)) return Common::response(0, '请传入产品id');

        $pay_money = $post['pay_money'] ?? 0;
        if(!is_numeric($pay_money) || $pay_money < 0) return Common::response(0, '下款金额填写不正常');

        $bridge = new Bridge();
        try{
            $product_info = $bridge->fetchProductInfo($product_id);
        }catch (\Exception $e){
            return Common::response(0, '网络异常');
        }

        if(isset($product_info['message'])) return Common::response(0, $product_info['message']);
        $type = (int)$product_info['settlement_type']; // 类型

        // 犀金会员默认白银级提升为黄金级别
        $level = UserModel::find()->select('user_level')->where(['id' => $this->userId])->scalar();
        $is_vip = Common::checkVip($this->userId);
        if($is_vip === true && $level == Bridge::SILVER) $level = Bridge::GOLD;

        if($type === Bridge::RETURN_RATE){
            switch ($level){
                case Bridge::SILVER:
                    preg_match('@^(.*)%$@',$product_info['silver_rate'],$matches);
                    $silver_rate = $matches[1];
                    $return_money = ($pay_money * $silver_rate)/100;
                    break;
                case Bridge::GOLD:
                    preg_match('@^(.*)%$@',$product_info['gold_rate'],$matches);
                    $gold_rate = $matches[1];
                    $return_money = ($pay_money * $gold_rate)/100;
                    break;
                case Bridge::DIAMOND:
                    preg_match('@^(.*)%$@',$product_info['diamond_rate'],$matches);
                    $diamond_rate = $matches[1];
                    $return_money = ($pay_money * $diamond_rate)/100;
                    break;
            }
        }
        elseif ($type === Bridge::RETURN_AWARD){ // 按照固定的金额来返现
            switch ($level){
                case Bridge::SILVER:
                    $return_money = $product_info['silver_award'];
                    break;
                case Bridge::GOLD:
                    $return_money = $product_info['gold_award'];
                    break;
                case Bridge::DIAMOND:
                    $return_money = $product_info['diamond_award'];
                    break;
            }
        }

        $now = date('Y-m-d H:i:s');
        $model =  UserDcReportModel::findOne($id);
        $model->setAttributes($post);
        $attr = [
            'leader_id' => $this->userId,
            'update_time' => $now,
            'status' => Config::REPORT_DOING,
            'return_money' => $return_money,
            'product_image' => $product_info['image'],
            'fail_reason' => ''
        ];

        $model->setAttributes($attr);

        if($model->save()) return Common::response(1, '操作成功');
        return Common::response(0, '操作失败', $model->getErrors());
    }

    public function actionUploadImg(){
        if (!$_FILES) return Common::response(0, '请选择文件');
        $file = $_FILES['image'];
        if (!$file || $file['error'] !== 0) return Common::response(0, '请选择文件');
        if ($file['size'] >= 1024 * 1024 * 10) return Common::response(1, '文件大小不能超过10MB');

        $file_temp = $file['tmp_name'];
        $file_name = md5(session_create_id() . uniqid());

        $ret = Common::uploadToAliyun_oss('',$file_temp, 'liebian/report/'. $file_name . '.jpg');
        $url = $ret['info']['url'] ?? '';

        return Common::response(1, '上传成功', $url);
    }


    /*
     * 下拉列表信息 获取到自己的下家列表
     * */
    public function actionGetUserInfo(){
        // 获取所有下家
        $user_query = UserRelationshipModel::find()->select('user.mobile,user.nick_name,user.id as user_id')
                                                   ->leftJoin('user','user.id = user_relationship.user_id')
                                                   ->where(['user_relationship.leader_id' => $this->getUserInfo()['id']])
                                                   ->orderBy('user_relationship.id desc');
        $data = $user_query->asArray()->all();
        return $data;
    }

}
