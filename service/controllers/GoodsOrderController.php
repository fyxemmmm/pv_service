<?php

namespace service\controllers;

use common\AliPay;
use common\Config;
use common\models\Common;
use common\models\GoodsLogisticsModel;
use common\models\GoodsOrderModel;
use common\models\GoodsOrderSubModel;
use common\WxPay;
use service\models\GoodsOrder;
use yii\data\ActiveDataProvider;

class GoodsOrderController extends CommonController
{
    public $modelClass = '';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'], $actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $status = $this->get('status');
        $goods_id = $this->get('goods_id');
        $order_sign = $this->get('order_sign');

        if (!empty($status)) {
            $status_arr = explode(',', $status);
        } else {
            $status_arr = [];
        }
        $query = GoodsOrder::find()->where(['is_del' => 0])
            ->andFilterWhere(['in', 'status', $status_arr])
            ->andFilterWhere(['like', 'order_sign', $order_sign]);

        if ($goods_id) {
            $goods_subs = GoodsOrderSubModel::find()
                ->select('goods_order_id')
                ->where(['goods_id' => $goods_id])
                ->asArray()
                ->all();

            if ($goods_subs) {
                $goods_order_ids = array_column($goods_subs, 'goods_order_id');
                $query->andWhere([
                    'or',
                    ['in', 'id', $goods_order_ids],
                    ['goods_id' => $goods_id]
                ]);
            } else {
                $query->andWhere(['goods_id' => $goods_id]);
            }
        }

        $query->orderBy('id desc'); // 用户未删除的

        $refund_status = $this->get('refund_status');
        if ($refund_status === "0") {
            $query->andWhere(['status' => Config::SHOP_ORDER_HAS_PAY, 'apply_for_refund' => 1]);
        } elseif ($refund_status === "1") {
            $query->andWhere(['status' => Config::SHOP_ORDER_HAS_DELIVERED]);
        }
        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    public function actionView($id)
    {
        $query = GoodsOrder::find()->where(['id' => $id]);
        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /*
    * 商品发货
    * */
    public function actionDeliverGood()
    {
        $goods_order_id = $this->post('goods_order_id');
        $com = $this->post('com'); // 圆通等
        $num = $this->post('num'); // 快递单号
        if (empty($com) || empty($num)) return Common::response(0, '快递单号数据/物流公司 不可为空');

        $phone = $this->post('phone');  // 发货人或者收货人手机

        $order_model = GoodsOrderModel::find()->where(['id' => $goods_order_id, 'status' => Config::SHOP_ORDER_HAS_PAY])->one();
        if (!$order_model) return Common::response(0, '仅能对买家已经付款的商品进行发货');
        if ($order_model->apply_for_refund == Config::APPLY_FOR_REFUND_DOING) return Common::response(0, '该商品正在退款中,请先处理退款业务');

        $transaction = GoodsOrderModel::getDb()->beginTransaction();

        try {
            $order_model->status = Config::SHOP_ORDER_HAS_DELIVERED; // 已发货
            $order_model->save();

            $model = new GoodsLogisticsModel();
            $model->com = $com;
            $model->num = $num;
            $model->phone = $phone;
            $model->goods_order_id = $goods_order_id;
            $model->create_time = date('Y-m-d H:i:s');
            $model->save();

            $transaction->commit();
            return Common::response(1, 'success');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return Common::response(0, '操作失败', $e->getMessage());
        }
    }

    /*
     * 获取发货的信息
     * */
    public function actionGetDeliverGood()
    {
        $goods_order_id = $this->get('goods_order_id');
        $goods_logistics_data = GoodsLogisticsModel::find()->where(['goods_order_id' => $goods_order_id])->asArray()->one();
        return $goods_logistics_data;
    }

    /*
     * 获取发货的信息
     * */
    public function actionUpdateDeliverGood()
    {
        $post = $this->post();
        $model = GoodsLogisticsModel::find()->where(['id' => $post['id']])->one();
        $model->setAttributes($post);
        $model->save();
        return Common::response(1, '操作成功');
    }

    /*
     * 退款逻辑业务  当用户在付完款待发货的状态进行退款的时候  后台审核退款
     * */
    public function actionAuditRefund()
    {
        $goods_order_id = $this->post('goods_order_id');
        $action = $this->post('action');
        $order_model = GoodsOrderModel::find()->where(['id' => $goods_order_id, 'status' => Config::SHOP_ORDER_HAS_PAY, 'apply_for_refund' => Config::APPLY_FOR_REFUND_DOING])->one();
        if (!$order_model) return Common::response(0, '未找到对象');
        if ($action == 'refund') { // 退款
            $pay_version = $order_model->pay_version;
            if ($pay_version == 'xijing_ios_wx' || $pay_version == 'xijing_android_wx' || $pay_version == 'xijing_ceshi_wx') {
                $wx = new WxPay();
            } else {
                if (empty($pay_version)) {
                    $ali = new AliPay();
                } else {
                    $ali = new AliPay($pay_version);
                }
            }


            $version = $order_model->version;
            if (empty($version)) {
                $total_price = $order_model->goods_price_total + $order_model->logistics_fee;
            } else if ($version == 'v2') { // v2
                $data = GoodsOrderSubModel::find()->select('goods_price_total,logistics_fee')->where(['goods_order_id' => $goods_order_id])->asArray()->all();
                $goods_price_total = array_sum(array_column($data, 'goods_price_total'));
                $logistics_fee = array_sum(array_column($data, 'logistics_fee'));
                $total_price = (string)($goods_price_total + $logistics_fee);
            } else {
                return Common::response(0, '该笔订单存在版本信息错误');
            }

            if ($pay_version == 'xijing_ios_wx' || $pay_version == 'xijing_android_wx' || $pay_version == 'xijing_ceshi_wx') {
                $res = $wx->refund($order_model->order_sign, $total_price);
            } else {
                $res = $ali->refund($order_model->order_sign, $total_price);
            }

            if ($res) {
                $order_model->status = Config::SHOP_ORDER_HAS_CANCELED;
                $order_model->update_time = date('Y-m-d H:i:s');
                $order_model->apply_for_refund = Config::APPLY_FOR_REFUND_NORMAL;
                $order_model->save();
                return Common::response(1, '退款成功');
            }
            if ($pay_version == 'xijing_ios_wx' || $pay_version == 'xijing_android_wx' || $pay_version == 'xijing_ceshi_wx') {
                return Common::response(0, '退款失败,微信第三方出现异常');
            } else {
                return Common::response(0, '退款失败,支付宝第三方出现异常');
            }

        } else if ($action == 'reject') { // 驳回
            $order_model->apply_for_refund = Config::APPLY_FOR_REFUND_NORMAL;
            $order_model->update_time = date('Y-m-d H:i:s');
            if ($order_model->save()) return Common::response(1, '驳回成功');
            return Common::response(0, '驳回失败', $order_model->getErrors());
        }
        return Common::response(0, '类型错误');
    }

    /*
     * 已经发货的商品 进行退款处理
     * */
    public function actionDirectRefund()
    {
        $goods_order_id = $this->post('goods_order_id');
        $order_model = GoodsOrderModel::find()->where(['id' => $goods_order_id, 'status' => Config::SHOP_ORDER_HAS_DELIVERED])->one();
        if (!$order_model) return Common::response(0, '未找到对象');

        $pay_version = $order_model->pay_version;
        if ($pay_version == 'xijing_ios_wx' || $pay_version == 'xijing_android_wx' || $pay_version == 'xijing_ceshi_wx') {
            $wx = new WxPay();
        } else {
            if (empty($pay_version)) {
                $ali = new AliPay();
            } else {
                $ali = new AliPay($pay_version);
            }
        }

        $version = $order_model->version;
        if (empty($version)) {
            $total_price = $order_model->goods_price_total + $order_model->logistics_fee;
        } else if ($version == 'v2') { // v2
            $data = GoodsOrderSubModel::find()->select('goods_price_total,logistics_fee')->where(['goods_order_id' => $goods_order_id])->asArray()->all();
            $goods_price_total = array_sum(array_column($data, 'goods_price_total'));
            $logistics_fee = array_sum(array_column($data, 'logistics_fee'));
            $total_price = (string)($goods_price_total + $logistics_fee);
        } else {
            return Common::response(0, '该笔订单存在版本信息错误');
        }
        if ($pay_version == 'xijing_ios_wx' || $pay_version == 'xijing_android_wx' || $pay_version == 'xijing_ceshi_wx') {
            $res = $wx->refund($order_model->order_sign, $total_price);
        } else {
            $res = $ali->refund($order_model->order_sign, $total_price);
        }
        if ($res) {
            $order_model->status = Config::SHOP_ORDER_HAS_CANCELED;  // 订单已取消
            $order_model->update_time = date('Y-m-d H:i:s');
            if ($order_model->save()) return Common::response(1, '退款成功');
            return Common::response(0, '退款失败', $order_model->getErrors());
        }
        if ($pay_version == 'xijing_ios_wx' || $pay_version == 'xijing_android_wx' || $pay_version == 'xijing_ceshi_wx') {
            return Common::response(0, '退款失败,微信第三方出现异常');
        } else {
            return Common::response(0, '退款失败,支付宝第三方出现异常');
        }
    }

    /*
     * 快递编号
     * */
    public function actionGetDeliverNum()
    {
        $res = [
            ['id' => 1, 'company_name' => '韵达快递', 'company_num' => 'yunda'],
            ['id' => 2, 'company_name' => '中通快递', 'company_num' => 'zhongtong'],
            ['id' => 3, 'company_name' => '顺丰速运', 'company_num' => 'shunfeng'],
            ['id' => 4, 'company_name' => '圆通速递', 'company_num' => 'yuantong'],
            ['id' => 5, 'company_name' => '德邦快递', 'company_num' => 'debangkuaidi'],
            ['id' => 6, 'company_name' => '申通快递', 'company_num' => 'shentong'],
            ['id' => 7, 'company_name' => '邮政国内', 'company_num' => 'youzhengguonei'],
        ];

        return $res;
    }

}
