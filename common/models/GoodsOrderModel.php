<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_order".
 *
 * @property int $id
 * @property string $order_sign 订单号
 * @property int $goods_id 商品id
 * @property string $goods_name 商品名字
 * @property int $goods_specification_id 规格id
 * @property string $goods_specification_name 商品规格
 * @property string $goods_image 商品图片
 * @property string $goods_price 商品价格(单价)
 * @property string $goods_price_total 该订单购买的总价格
 * @property string $logistics_fee 物流费
 * @property string $goods_service 商品提供什么服务
 * @property int $user_id 用户id
 * @property string $user_name 用户表单提交的名字
 * @property string $mobile 用户表单提交的手机号
 * @property int $number 购买商品的数量
 * @property string $receive_addr 收货地址
 * @property string $remarks 订单备注
 * @property int $status 1待支付 2已支付 3待收货 4已完成 5已取消
 * @property int $apply_for_refund 只有在已支付的时候(2) 1 代表正在申请退款
 * @property string $channel 渠道 支付宝、微信
 * @property string $order_detail 用于给支付宝的字符串信息
 * @property string $auto_cancel_time 如果没有付款，自动取消订单的时间点
 * @property string $version 版本
 * @property string $pay_version 支付版本
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property int $is_del 软删字段
 * @property string $coupon_code 所使用的优惠券
 * @property int $coupon_sta 0使用1返回原账户
 */
class GoodsOrderModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'goods_specification_id', 'user_id', 'number', 'status', 'apply_for_refund', 'is_del', 'coupon_sta'], 'integer'],
            [['goods_price', 'goods_price_total', 'logistics_fee'], 'number'],
            [['channel'], 'required'],
            [['order_detail'], 'string'],
            [['auto_cancel_time', 'create_time', 'update_time'], 'safe'],
            [['order_sign', 'goods_name', 'goods_specification_name', 'goods_image', 'goods_service', 'user_name', 'mobile', 'receive_addr', 'remarks', 'channel', 'coupon_code'], 'string', 'max' => 255],
            [['version', 'pay_version'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_sign' => '订单号',
            'goods_id' => '商品id',
            'goods_name' => '商品名字',
            'goods_specification_id' => '规格id',
            'goods_specification_name' => '商品规格',
            'goods_image' => '商品图片',
            'goods_price' => '商品价格(单价)',
            'goods_price_total' => '该订单购买的总价格',
            'logistics_fee' => '物流费',
            'goods_service' => '商品提供什么服务',
            'user_id' => '用户id',
            'user_name' => '用户表单提交的名字',
            'mobile' => '用户表单提交的手机号',
            'number' => '购买商品的数量',
            'receive_addr' => '收货地址',
            'remarks' => '订单备注',
            'status' => '1待支付 2已支付 3待收货 4已完成 5已取消',
            'apply_for_refund' => '只有在已支付的时候(2) 1 代表正在申请退款',
            'channel' => '渠道 支付宝、微信',
            'order_detail' => '用于给支付宝的字符串信息',
            'auto_cancel_time' => '如果没有付款，自动取消订单的时间点',
            'version' => '版本',
            'pay_version' => '支付版本',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'is_del' => '软删字段',
            'coupon_code' => '所使用的优惠券',
            'coupon_sta' => '0使用1返回原账户',
        ];
    }
}
