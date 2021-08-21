<?php

namespace service\controllers;

use common\models\GoodsSpecificationModel;
use common\models\GoodsTypeModel;
use common\models\UserDcOrderModel;
use yii\data\ActiveDataProvider;
use common\models\Common;
use Yii;
use common\models\GoodsModel;
use common\models\UserModel;
use common\models\AdminModel;
use common\models\GoodsClickModel;
use common\models\GoodsOrderModel;
use common\models\GoodsOrderSubModel;
use service\models\Admin;
use service\models\Goods;

class GoodsAnalyzeController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    /**
     * 商品数据统计
     *
     */
    public function actionGoodsData()
    {
        $admin_id = $this->get('admin_id');
        $start_time = $this->get('start_time');
        $end_time = $this->get('end_time');
        $perPage = 20;
        $page = $this->get('page', 1);
        //日均UV天数
        if($this->get('start_time') && $this->get('end_time')){
            $begin_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $days = round(($end_date - $begin_date) / 3600 / 24) + 1;
        }elseif ($this->get('start_time')){
            $begin_date = strtotime($start_time);
            $end_date = strtotime(date("Y-m-d"));
            $days = round(($end_date - $begin_date) / 3600 / 24) + 1;
        }else{
            $days = 0;
        }
        //商品相关信息
        $query = GoodsModel::find()
            //->alias('a')
            //->select('a.id,a.name,a.creator_id,a.create_time,b.name as type_name,c.original_cost,d.realname')
            //->leftJoin('goods_type b', 'a.goods_type_id = b.id')
            //->leftJoin('goods_specification c', 'a.id = c.goods_id')
            //->leftJoin('admin d','d.id = a.creator_id')
            ->andFilterWhere(['creator_id'=>$admin_id])
            ->orderBy('id desc,create_time desc');
            //->asArray()
            //->all();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $models = $dataProvider->getModels();

        $rows = [];
        foreach ($models as $key => $val) {
            $rows[$key] = $val->attributes;
            $rows[$key]['type_name'] = GoodsTypeModel::find()->select('name')->where(['id'=>(int)$val->goods_type_id])->scalar();
            $rows[$key]['original_cost'] = GoodsSpecificationModel::find()->select('original_cost')->where(['goods_id'=>(int)$val->id])->scalar();
            $rows[$key]['realname'] = AdminModel::find()->select('realname')->where(['id'=>(int)$val->goods_type_id])->scalar();
            //商品的总UV，PV去重
            $click_count = GoodsClickModel::find()
                ->where(['goods_id'=>(int)$val->id])
                ->andFilterWhere(['between', 'create_time', $start_time, $end_time])
                ->groupBy('user_id')
                ->count();
            $rows[$key]['click_count'] = $click_count;
            //商品日均UV
            if($days>0){
                $rows[$key]['click_one_count'] = round($rows[$key]['click_count']/$days,2);
            }else{
                $rows[$key]['click_one_count'] = '-';
            }
            //商品下单数量(商品总数)
            $order_count = GoodsOrderSubModel::find()
                ->leftJoin('goods_order', 'goods_order.id = goods_order_sub.goods_order_id')
                ->andwhere(['goods_order_sub.goods_id'=>(int)$val->id])
                ->andFilterWhere(['between', 'goods_order.create_time', $start_time, $end_time])
                ->sum('goods_order_sub.number');
            $rows[$key]['order_count'] = $order_count;
            //商品成单数量(商品总数)
            $order_success_count = GoodsOrderSubModel::find()
                ->leftJoin('goods_order', 'goods_order.id = goods_order_sub.goods_order_id')
                ->andwhere(['goods_order_sub.goods_id'=>(int)$val->id])
                ->andwhere(['goods_order.status'=>4])
                ->andFilterWhere(['between', 'goods_order.create_time', $start_time, $end_time])
                ->sum('goods_order_sub.number');
            $rows[$key]['order_success_count'] = $order_success_count;
        }
        //排序规则  商品日均UV 1↓2↑ 下单量 3↓4↑ 成交量 5↓6↑
        if($this->get('order')){
            $order = $this->get('order');
            if($order == 1){
                array_multisort(array_column($rows,'click_one_count'),SORT_DESC,$rows);
            }elseif ($order == 2){
                array_multisort(array_column($rows,'click_one_count'),SORT_ASC,$rows);
            }elseif ($order == 3){
                array_multisort(array_column($rows,'order_count'),SORT_DESC,$rows);
            }elseif ($order == 4){
                array_multisort(array_column($rows,'order_count'),SORT_ASC,$rows);
            }elseif ($order == 5){
                array_multisort(array_column($rows,'order_success_count'),SORT_DESC,$rows);
            }elseif ($order == 6){
                array_multisort(array_column($rows,'order_success_count'),SORT_ASC,$rows);
            }
        }

        //$count = count($rows);
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

    /**
     * 上货用户考核信息统计
     *
     */
    public function actionCheckData()
    {
        $admin_id = $this->get('admin_id');
        $start_time = $this->get('start_time');
        $end_time = $this->get('end_time');
        $perPage = 10;
        $page = $this->get('page', 1);
        //$cache = Yii::$app->cache;
        //$rows = $cache->get('check_data');
        //if ($rows === false) {
        $query = AdminModel::find()
            ->select('id,name,realname')
            ->andFilterWhere(['id'=>$admin_id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $models = $dataProvider->getModels();
        $rows = [];
        foreach ($models as $key => $val) {
            $rows[$key] = $val->attributes;
            //每个上传用户的产品集合
            $goods_arr = GoodsModel::find()
                ->where(['creator_id'=>(int)$val->id])
                ->andFilterWhere(['between', 'create_time', $start_time, $end_time])
                ->column();
            //每个上传用户的产品数量
            $rows[$key]['goods_count'] = count($goods_arr);
            $rows[$key]['goods_uv'] = 0;

            $click_count = GoodsClickModel::find()
                ->where(['in','goods_id',$goods_arr])
                ->andFilterWhere(['between', 'create_time', $start_time, $end_time])
                ->groupBy('user_id')
                ->count();
            $rows[$key]['goods_uv'] = (int)$click_count;
            /*
            foreach ($goods_arr as $val1){
                //用户上传产品UV
                $click_count = GoodsClickModel::find()
                    ->where(['goods_id'=>(int)$val1])
                    ->andFilterWhere(['between', 'create_time', $start_time, $end_time])
                    ->groupBy('user_id')
                    ->count();
                $rows[$key]['goods_uv'] += (int)$click_count;
            }
            */
            //件均UV
            if($rows[$key]['goods_count'] == 0){
                $rows[$key]['goods_one_uv'] = 0;
            }else{
                $rows[$key]['goods_one_uv'] = round($rows[$key]['goods_uv']/$rows[$key]['goods_count'],2);
            }
            //用户上传产品下单量(订单数量去重)
            $goods_order = GoodsOrderSubModel::find()
                ->leftJoin('goods_order','goods_order.id = goods_order_sub.goods_order_id')
                ->andwhere(['in','goods_order_sub.goods_id',$goods_arr])
                ->andFilterWhere(['between', 'goods_order.create_time', $start_time, $end_time])
                ->groupBy('goods_order_id')
                ->count();
            $rows[$key]['goods_order'] = $goods_order;
            //用户上传产品成单量(订单数量去重)
            $goods_success_order = GoodsOrderSubModel::find()
                ->leftJoin('goods_order','goods_order.id = goods_order_sub.goods_order_id')
                ->andwhere(['in','goods_order_sub.goods_id',$goods_arr])
                ->andFilterWhere(['between', 'goods_order.create_time', $start_time, $end_time])
                ->andwhere(['goods_order.status'=>4])
                ->groupBy('goods_order_sub.goods_order_id')
                ->count();
            $rows[$key]['goods_success_order'] = $goods_success_order;
        }
        //排序规则  商品件均UV 1↓2↑ 下单量 3↓4↑ 成交量 5↓6↑
        if($this->get('order')){
            $order = $this->get('order');
            if($order == 1){
                array_multisort(array_column($rows,'goods_one_uv'),SORT_DESC,$rows);
            }elseif ($order == 2){
                array_multisort(array_column($rows,'goods_one_uv'),SORT_ASC,$rows);
            }elseif ($order == 3){
                array_multisort(array_column($rows,'goods_order'),SORT_DESC,$rows);
            }elseif ($order == 4){
                array_multisort(array_column($rows,'goods_order'),SORT_ASC,$rows);
            }elseif ($order == 5){
                array_multisort(array_column($rows,'goods_success_order'),SORT_DESC,$rows);
            }elseif ($order == 6){
                array_multisort(array_column($rows,'goods_success_order'),SORT_ASC,$rows);
            }
        }
        //$cache->set('check_data', $rows, 3600);
        //}

        //$count = count($rows);
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

    /**
     * 管理员列表
     *
     */
    public function actionAdminList()
    {
        //上货人列表
        $rows = AdminModel::find()
            ->select('id,name,realname')
            ->asArray()
            ->all();
        $count = count($rows);
        $perPage = 50;

        return [
            'items' => $rows,
            '_meta' => [
                'totalCount' => $count,
                'pageCount' => ceil($count / $perPage)
            ]
        ];
    }

}
