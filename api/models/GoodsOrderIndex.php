<?php

namespace api\models;

use common\Config;
use common\Helper;
use common\models\GoodsOrderModel;
use common\models\CouponUserModel;
use common\models\GoodsOrderSubModel;
use common\models\GoodsSpecificationModel;

class GoodsOrderIndex extends GoodsOrderModel
{

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['goods_service']);
        unset($fields['user_id']);
        unset($fields['user_name']);
        unset($fields['mobile']);
        unset($fields['receive_addr']);
        unset($fields['update_time']);
        unset($fields['is_del']);
        unset($fields['auto_cancel_time']);

        // 只有待发货的状态,会有申请退款的这个标记
        $fields['apply_for_refund'] = function (){
            return $this->apply_for_refund == Config::APPLY_FOR_REFUND_DOING ? true : false;
        };

        $fields['specification_list'] = function (){
            $version = $this->version;
            if(empty($version)){
                $data = [
                    [
                        'goods_id' => $this->goods_id,
                        'goods_name' => $this->goods_name,
                        'goods_specification_id' => $this->goods_specification_id,
                        'goods_specification_name' => $this->goods_specification_name,
                        'goods_price' => $this->goods_price,
                        'number' => $this->number,
                        'specification_img' => $this->goods_image,
                        'image_url' => $this->goods_image,
                        'price' => $this->goods_price,
                        'specification_id' => $this->goods_specification_id,
                        'specification' => $this->goods_specification_name,
                    ]
                ];
                Helper::formatData($data,2);

            }else if ($version == 'v2'){
                $data = GoodsOrderSubModel::find()->select('goods_id,goods_name,goods_specification_id,goods_specification_name,goods_price,number')->where(['goods_order_id' => $this->id])->asArray()->all();
                foreach ($data as $k=>&$v){
                    $specification_img = GoodsSpecificationModel::find()->select('image_url')->where(['id' => $v['goods_specification_id']])->scalar();
                    $v['specification_img'] = $specification_img;

                    // 兼容
                    $v['image_url'] = $specification_img;
                    $v['price'] = $v['goods_price'];
                    $v['specification_id'] = $v['goods_specification_id'];
                    $v['specification'] = $v['goods_specification_name'];
                }
            }

            return $data;
        };

        // 总件数
        $fields['total_goods_number'] = function (){
            $version = $this->version;
            if(empty($version)){
                return (string)$this->number;
            }else if ($version == 'v2'){
                $data = GoodsOrderSubModel::find()->select('number')->where(['goods_order_id' => $this->id])->asArray()->all();
                return (string)array_sum(array_column($data,'number'));
            }
        };

        // 实付
        $fields['goods_real_pay'] = function (){
            $version = $this->version;
            if(empty($version)){
                return (string)($this->goods_price_total + $this->logistics_fee);
            }else if ($version == 'v2'){
                $data = GoodsOrderSubModel::find()->select('goods_price_total,logistics_fee')->where(['goods_order_id' => $this->id])->asArray()->all();
                $goods_price_total = array_sum(array_column($data,'goods_price_total'));
                $logistics_fee =  array_sum(array_column($data,'logistics_fee'));
                $time = date('Y-m-d H:i:s');
                $coupon = CouponUserModel::find()
                    ->alias('a')
                    ->select('b.amount,b.limit_amount,b.coupon_code')
                    ->leftJoin('coupon b', 'a.coupon_code = b.coupon_code')
                    ->where(['a.coupon_code' => $this->coupon_code, 'a.coupon_status' => 3])
                    ->asArray()
                    ->one();
                if($coupon) {
                    $goods_price_total = $goods_price_total - (float)$coupon['amount'];
                }
                return (string)($goods_price_total + $logistics_fee);
            }
        };

        return $fields;
    }

}
