<?php

namespace service\controllers;

use common\models\GoodsSpecificationModel;
use yii\data\ActiveDataProvider;
use common\models\Common;
use common\models\GoodsModel;
use service\models\Admin;
use service\models\Goods;

class GoodsController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $admin_id = $this->get('admin_id');
        $name = $this->get('name');
        $goods_type_id = $this->get('type');
        $status = $this->get('status');
        $perPage = $this->get('perPage', 20);
        $page = $this->get('page', 1);

        $query = GoodsModel::find()
            ->andFilterWhere(['like', 'name', $name])
            ->andFilterWhere(['goods_type_id' => $goods_type_id])
            ->andFilterWhere(['status' => $status])
            ->andFilterWhere(['creator_id' => $admin_id])
            ->orderBy('top_time desc, id desc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $models = $dataProvider->getModels();

        $rows = [];
        foreach ($models as $key => $obj) {
            $rows[$key] = $obj->attributes;
            if ($obj->creator_id) {
                $admin = Admin::findOne($obj->creator_id);
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

    public function actionView($id)
    {
        $query = Goods::find()->where(['id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

    public function actionCreate()
    {
        $post = $this->post();
        $message = $this->checkParams($post,'create');
        if($message !== true) return Common::response(0, $message);

        $post['create_time'] = date('Y-m-d H:i:s');
        $post['status'] = 0;
        $post['detail_content'] = htmlspecialchars($post['detail_content'] ?? '');

        $model = new GoodsModel();
        $post['creator_id'] = $this->adminId;
        $model->setAttributes($post);
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0,'failure', $model->getErrors());
    }

    public function actionUpdate($id)
    {
        $update_data = $this->post();
        if(count($update_data) == 1 && isset($update_data['status'])){
            $model = GoodsModel::find()->where(['id' => $id])->one();  // 当前商品模型
            if($update_data['status'] == 1){
                $has_sp = GoodsSpecificationModel::find()->where(['goods_id' => $id])->one(); // 商品下必须有规格
                if(!$has_sp) return Common::response(0,'上架商品之前,请先添加一个规格信息');
            }
            $model->setAttributes($update_data);
            if($model->save()) return Common::response(1, 'success');
            return Common::response(0,'failure', $model->getErrors());
        } else if (count($update_data) == 1 && isset($update_data['top_time'])) { // 置顶
            $model = GoodsModel::findOne(['id' => $id]);  // 当前商品模型
            $model->setAttribute('top_time', $update_data['top_time']);
            if ($model->save()) return Common::response(1, 'success');
            return Common::response(0, 'failure', $model->getErrors());
        }

        $message = $this->checkParams($update_data,'update');
        if($message !== true) return Common::response(0, $message);

        $update_data['detail_content'] = htmlspecialchars($update_data['detail_content'] ?? '');
        $update_data['update_time'] = date('Y-m-d H:i:s');

        $model = GoodsModel::find()->where(['id' => $id])->one();  // 当前商品模型

        $gs_data = GoodsSpecificationModel::find()->where(['goods_id' => $id])->asArray()->all(); // 这个商品下面已有的规格
        $goods_info = [
            'good_tax' => $update_data['good_tax'],
            'profitable_rate' => $update_data['profitable_rate'],
            'logistics_fee' => $update_data['logistics_fee'],
            'discount' => $update_data['discount'],
        ];

        $transaction = GoodsSpecificationModel::getDb()->beginTransaction();
        try{
            foreach ($gs_data as $k=>$v){
                $purchasing_cost = $v['purchasing_cost'];
                list($original_cost, $after_discount_cost) = Common::calcPrice($purchasing_cost, $goods_info);
                $gs_model = GoodsSpecificationModel::find()->where(['id' => $v['id']])->one();
                $gs_model->original_cost = $original_cost;
                $gs_model->after_discount_cost = $after_discount_cost;
                $gs_model->save();
            }
            $model->setAttributes($update_data);
            $model->save();
            $transaction->commit();
            return Common::response(1, 'success');
        }catch(\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        }

    }

    public function checkParams($data, $type){
        if(count($data) == 1 &&  $type == 'update') {
            return true; // 单个状态不检查
        }
        if(!isset($data['discount']) || !is_numeric($data['discount']) || $data['discount'] < 0){
            return "会员折扣参数有误,请检查";
        }

        if(!isset($data['profitable_rate']) || !is_numeric($data['profitable_rate']) || $data['profitable_rate'] < 0){
            return "盈利百分比参数有误,请检查";
        }

        if(!isset($data['good_tax']) || !is_numeric($data['good_tax']) || $data['good_tax'] < 0){
            return "商品税利率参数有误,请检查";
        }

        if(!isset($data['logistics_fee']) || !is_numeric($data['logistics_fee']) || $data['logistics_fee'] < 0){
            return "邮费参数填写有误,请检查";
        }

        return true;
    }

}
