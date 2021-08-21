<?php

namespace service\controllers;

use common\models\CouponBannerModel;
use common\models\UserModel;
use yii\data\ActiveDataProvider;
use common\models\Common;
use common\models\CouponModel;
use common\models\CouponUserModel;
use common\models\FeedbackModel;
use service\models\Admin;
use service\models\Goods;
use common\models\BannerModel;

class CouponController extends CommonController
{
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index']);
        return $actions;
    }

    //优惠券列表
    public function actionIndex()
    {
        $admin_id = $this->get('admin_id');
        $name = $this->get('name');
        $goods_type_id = $this->get('type');
        $status = $this->get('status');
        $perPage = $this->get('perPage', 20);
        $page = $this->get('page', 1);

        $query = CouponModel::find()
            ->andFilterWhere(['like', 'name', $name])
            ->andFilterWhere(['type_id' => $goods_type_id])
            ->andFilterWhere(['status' => $status])
            ->andFilterWhere(['admin_id' => $admin_id])
            ->orderBy('create_time desc, id desc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $models = $dataProvider->getModels();

        $rows = [];
        foreach ($models as $key => $obj) {
            $rows[$key] = $obj->attributes;
            if ($obj->admin_id) {
                $admin = Admin::findOne($obj->admin_id);
                $admin_mobile = $admin ? $admin->name : '';
                $rows[$key]['admin_mobile'] = $admin_mobile;
                $rows[$key]['admin_id'] = $admin['id'];
                $rows[$key]['realname'] = $admin['realname'];
            }
        }

        $count = $query->count();

        return [
            'items' => $rows,
            '_meta' => [
                'totalCount' => $count,
                'pageCount' => ceil($count / $perPage),
                'currentPage' => $page
            ]
        ];
    }

    //优惠券详情
    public function actionView($id)
    {
        $query = CouponModel::find()->where(['id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    //添加优惠券
    public function actionCreate()
    {
        $post = $this->post();
        $message = $this->checkParams($post, 'create');
        if ($message !== true) return Common::response(0, $message);
        $data['create_time'] = date('Y-m-d H:i:s');
        if(isset($post['type'])){
            $data['type_id'] = $post['type'];
        }
        $data['status'] = $post['status'];
        $data['overdue'] = $post['overdue'];
        $data['amount'] = $post['amount'];
        $data['limit_amount'] = $post['limit_amount'];
        $data['name'] = htmlspecialchars($post['name'] ?? '');
        $data['description'] = htmlspecialchars($post['description'] ?? '');
        $code_arr = $this->getcode(1, '', 12, 'xj');
        $data['coupon_code'] = $code_arr[0];
        $model = new CouponModel();
        $data['admin_id'] = $this->adminId;
        $model->setAttributes($data);
        if ($model->save(false)) return Common::response(1, 'success');
        return Common::response(0, 'failure', $model->getErrors());
    }

    //修改优惠券
    public function actionUpdate($id)
    {
        $update_data = $this->post();
        $message = $this->checkParams($update_data, 'update');
        if ($message !== true) return Common::response(0, $message);
        if(count($update_data) == 1 && isset($update_data['status'])){
            $model = CouponModel::find()->where(['id' => $id])->one();
            $data['status'] = $update_data['status'];
            $model->setAttributes($data);
            if($model->save()) return Common::response(1, 'success');
            return Common::response(0,'failure', $model->getErrors());
        }
        if(isset($update_data['type'])){
            $data['type_id'] = $update_data['type'];
        }
        $data['name'] = htmlspecialchars($update_data['name'] ?? '');
        $data['description'] = htmlspecialchars($update_data['description'] ?? '');
        $data['update_time'] = date('Y-m-d H:i:s');
        $data['limit_amount'] = $update_data['limit_amount'];

        $model = CouponModel::find()->where(['id' => $id])->one();  //当前优惠券模型

        $model->setAttributes($data);
        $model->save(false);
        return Common::response(1, 'success');

    }

    //删除优惠券
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return Common::response(1, 'success');
    }

    /**
     * Finds the BannerModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BannerModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CouponModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function getCode($nums, $exist_array = '', $code_length = 12, $prefix = '')
    {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $promotion_codes = array();//这个数组用来接收生成的优惠码
        for ($j = 0; $j < $nums; $j++) {
            $code = '';
            for ($i = 0; $i < $code_length; $i++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
            if (!in_array($code, $promotion_codes)) {
                if (is_array($exist_array)) {
                    if (!in_array($code, $exist_array)) {//排除已经使用的优惠码
                        $promotion_codes[$j] = $prefix . $code;
                    } else {
                        $j--;
                    }
                } else {
                    $promotion_codes[$j] = $prefix . $code;
                }
            } else {
                $j--;
            }
        }

        return $promotion_codes;
    }

    public function actionGetCoupon(){
        $query = CouponBannerModel::find()->where(['or',['type' => 1],['type' => 2]]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionGetDetail(){
        $id = $this->get('id');
        $query = CouponBannerModel::find()->where(['id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionEditBanner(){
        $id = $this->post('id');
        $image_url = $this->post('image_url');
        $share_url = $this->post('share_url');
        $web_url = $this->post('web_url');
        $title = $this->post('title');
        $share_title = $this->post('share_title');
        $share_desc = $this->post('share_desc');
        $model = CouponBannerModel::find()->where(['id' => (int)$id])->one();
        $model->img_url = $image_url;
        $model->share_url = $share_url;
        $model->web_url = $web_url;
        $model->title = $title;
        $model->share_title = $share_title;
        $model->share_desc = $share_desc;
        if($model->save()) return Common::response(1, '更新成功');
        return Common::response(0, '更新失败', $model->getErrors());
    }

    public function checkParams($data, $type)
    {
        if (count($data) == 1 && $type == 'update') {
            return true; // 单个状态不检查
        }
        if (!isset($data['name']) || strlen($data['name']) < 2) {
            return "优惠券名称参数有误,请检查!";
        }
        /*
        if (!isset($data['type']) || $data['type'] < 0) {
            return "优惠券分类参数有误,请检查!";
        }
        */
        if(!isset($data['']) || $data['overdue'] < 0){
            return "优惠券失效参数有误,请检查!";
        }
        if (!isset($data['overdue']) || $data['overdue'] < 0) {
            return "优惠券失效参数有误,请检查!";
        }
        if (!isset($data['amount']) || $data['amount'] < 0) {
            return "优惠券抵扣金额小于0,请检查!";
        }
        if (!isset($data['limit_amount']) || $data['limit_amount'] < 0) {
            return "优惠券最低可用价格小于0,请检查!";
        }

        return true;
    }

    public function actionAdduser()
    {
        /*
        $feed = FeedbackModel::find()->asArray()->all();
        foreach ($feed as $key=>$val){
            $mobile = UserModel::find()->select('mobile')->where(['id'=>$val['user_id']])->scalar();
            FeedbackModel::updateAll(['mobile'=>$mobile],['user_id'=>$val['user_id']]);
        }
        */
    }

}
