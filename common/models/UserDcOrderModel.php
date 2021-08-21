<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_dc_order".
 *
 * @property int $id
 * @property int $return_type 类型 0导表返现，1报备返现
 * @property int $product_id 产品id
 * @property string $product_name 甲方产品名
 * @property string $product_image 产品的图片
 * @property int $leader_id 上家用户id
 * @property int $user_id 下家用户id
 * @property string $pay_money 该下家用户在甲方平台下款的金额
 * @property string $return_money 该返回给上家的佣金
 * @property string $order_sign 订单号
 * @property int $status 状态 针对的是甲方那边 0是未下款 1是甲方在审核 2是已在甲方下款
 * @property int $pass_audit 是否是从审核中变为已下款的 0否，1是
 * @property string $create_time
 * @property string $update_time
 * @property string $new_time 小于这个时间，就认为是新订单
 */
class UserDcOrderModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_dc_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['return_type', 'product_id', 'leader_id', 'user_id', 'status', 'pass_audit'], 'integer'],
            [['pay_money', 'return_money'], 'number'],
            [['create_time', 'update_time', 'new_time'], 'safe'],
            [['product_name', 'product_image', 'order_sign'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'return_type' => '类型 0导表返现，1报备返现',
            'product_id' => '产品id',
            'product_name' => '甲方产品名',
            'product_image' => '产品的图片',
            'leader_id' => '上家用户id',
            'user_id' => '下家用户id',
            'pay_money' => '该下家用户在甲方平台下款的金额',
            'return_money' => '该返回给上家的佣金',
            'order_sign' => '订单号',
            'status' => '状态 针对的是甲方那边 0是未下款 1是甲方在审核 2是已在甲方下款',
            'pass_audit' => '是否是从审核中变为已下款的 0否，1是',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'new_time' => '小于这个时间，就认为是新订单',
        ];
    }
}
