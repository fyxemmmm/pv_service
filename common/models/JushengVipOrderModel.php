<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jusheng_vip_order".
 *
 * @property int $id
 * @property int $user_id
 * @property string $order_sign 订单号
 * @property string $price 交易的金额
 * @property int $vip_type 1 90天季卡，2 终生会员
 * @property int $pay_type 支付类型 1 支付宝  2 微信
 * @property int $trade_status 支付状态0失败1成功
 * @property string $create_time
 * @property string $update_time
 */
class JushengVipOrderModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jusheng_vip_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'vip_type', 'pay_type', 'trade_status'], 'integer'],
            [['price'], 'number'],
            [['pay_type'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['order_sign'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'order_sign' => '订单号',
            'price' => '交易的金额',
            'vip_type' => '1 90天季卡，2 终生会员',
            'pay_type' => '支付类型 1 支付宝  2 微信',
            'trade_status' => '支付状态0失败1成功',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
