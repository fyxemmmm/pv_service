<?php

namespace api\models;

use common\Config;
use common\models\Common;
use common\models\GoodsOrderModel;
use common\models\CouponModel;
use common\models\GoodsLogisticsModel;
use common\models\GoodsOrderSubModel;
use common\models\GoodsSpecificationModel;

class GoodsOrderView extends GoodsOrderModel
{

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['service']);
        unset($fields['user_id']);
        unset($fields['goods_service']);
        unset($fields['is_del']);
        unset($fields['update_time']);

        // 待支付剩余时间，一天之后取自动取消订单
        $fields['cancel_time_left'] = function (){
            if(!$this->auto_cancel_time) return "86400"; // 防止报错
            return (string)(strtotime($this->auto_cancel_time) - time());
        };

        // 只有待发货的状态,会有申请退款的这个标记
        $fields['apply_for_refund'] = function (){
            return $this->apply_for_refund == Config::APPLY_FOR_REFUND_DOING ? true : false;
        };

        // 只有待发货的状态,会有申请退款的这个标记
        $fields['logistics_info'] = function (){
            // 已发货/已完成下有可能会有
            if($this->status != Config::SHOP_ORDER_HAS_DELIVERED && $this->status != Config::SHOP_ORDER_HAS_FINISHED) return [];
            $glm = GoodsLogisticsModel::find()->where(['goods_order_id' => $this->id])->one();
            if($glm){
                $res = Common::getLogisticsInfo($glm['com'], $glm['num']);
                if(isset($res['result']) && $res['result'] === false) return [];
                foreach ($res['data'] as $k=>&$v){
                    $time = strtotime($v['time']);
                    $v['time_one'] = date('m-d', $time);
                    $v['time_two'] = date('H:i', $time);
                }
                return $res['data'];
            }else{
                return [];
            }
        };

        $fields['specification_list'] = function (){
            $specification_img = GoodsSpecificationModel::find()->select('image_url')->where(['id' => $this->goods_specification_id])->scalar();

            return [[
                'goods_id' => $this->goods_id,
                'goods_name' => $this->goods_name,
                'goods_specification_id' => $this->goods_specification_id,
                'goods_specification_name' => $this->goods_specification_name,
                'goods_price' => $this->goods_price,
                'number' => $this->number,
                'specification_img' => $specification_img
            ]];
        };

        $fields['coupon_amount'] = function (){
            $coupon_code = parent::find()->select('coupon_code')->where(['id' => $this->id])->scalar();
            $amount = CouponModel::find()->select('amount')->where(['coupon_code' => $coupon_code])->scalar();
            return $amount;
        };

        return $fields;
    }

}
