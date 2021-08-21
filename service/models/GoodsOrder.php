<?php

namespace service\models;
use common\Config;
use common\models\GoodsOrderModel;
use common\models\GoodsOrderSubModel;

class GoodsOrder extends GoodsOrderModel
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['order_detail']);
        unset($fields['is_del']);

        $fields['is_delivered'] = function (){
            if($this->status == Config::SHOP_ORDER_HAS_DELIVERED || $this->status == Config::SHOP_ORDER_HAS_FINISHED) return true;
            return false;
        };

        $fields['refund_status'] = function (){
            switch ($this->status){
                case Config::SHOP_ORDER_HAS_PAY:
                    $refunding_status = GoodsOrderModel::find()->where(['status' => Config::SHOP_ORDER_HAS_PAY, 'apply_for_refund' => Config::APPLY_FOR_REFUND_DOING, 'id' => $this->id])->one() ? 0 : 2;
                    return $refunding_status;
                case Config::SHOP_ORDER_HAS_DELIVERED:
                    return 1; // 商家主动退款
                default:
                    return 2;
            }
        };

        $fields['order_sub'] = function (){
            if ('v2' == $this->version) {
                $list = GoodsOrderSubModel::find()
                    ->where(['goods_order_id' => $this->id])
                    ->asArray()
                    ->all();

                return $list;
            } else {
                return [];
            }
        };

        return $fields;
    }

}
