<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_logistics".
 *
 * @property int $id
 * @property int $goods_order_id 商品订单id
 * @property string $com 快递公司的单号
 * @property string $num 快递单号
 * @property string $phone 手机号，顺丰快递必填
 * @property string $create_time
 * @property string $receive_time 收货时间
 */
class GoodsLogisticsModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_logistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_order_id'], 'integer'],
            [['create_time', 'receive_time'], 'safe'],
            [['com', 'num', 'phone'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_order_id' => '商品订单id',
            'com' => '快递公司的单号',
            'num' => '快递单号',
            'phone' => '手机号，顺丰快递必填',
            'create_time' => 'Create Time',
            'receive_time' => '收货时间',
        ];
    }
}
