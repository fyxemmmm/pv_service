<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_order_sub".
 *
 * @property int $id
 * @property int $goods_order_id
 * @property int $goods_id
 * @property string $goods_name
 * @property int $goods_specification_id
 * @property string $goods_specification_name
 * @property string $goods_image
 * @property string $goods_price
 * @property string $goods_price_total
 * @property string $logistics_fee
 * @property string $goods_service
 * @property int $number
 */
class GoodsOrderSubModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_order_sub';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_order_id', 'goods_id', 'goods_specification_id', 'number'], 'integer'],
            [['goods_price', 'goods_price_total', 'logistics_fee'], 'number'],
            [['goods_name', 'goods_specification_name', 'goods_image', 'goods_service'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_order_id' => 'Goods Order ID',
            'goods_id' => 'Goods ID',
            'goods_name' => 'Goods Name',
            'goods_specification_id' => 'Goods Specification ID',
            'goods_specification_name' => 'Goods Specification Name',
            'goods_image' => 'Goods Image',
            'goods_price' => 'Goods Price',
            'goods_price_total' => 'Goods Price Total',
            'logistics_fee' => 'Logistics Fee',
            'goods_service' => 'Goods Service',
            'number' => 'Number',
        ];
    }
}
