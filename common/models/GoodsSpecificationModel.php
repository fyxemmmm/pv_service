<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_specification".
 *
 * @property int $id
 * @property int $goods_id
 * @property string $main_specification 主要的规格的值
 * @property string $second_specification 次要的规格的值
 * @property string $name
 * @property string $image_url
 * @property string $purchasing_cost 进货价、成本价
 * @property string $original_cost 原价
 * @property string $after_discount_cost 会员价
 */
class GoodsSpecificationModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_specification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
            [['purchasing_cost', 'original_cost', 'after_discount_cost'], 'number'],
            [['main_specification', 'second_specification', 'name', 'image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'main_specification' => '主要的规格的值',
            'second_specification' => '次要的规格的值',
            'name' => 'Name',
            'image_url' => 'Image Url',
            'purchasing_cost' => '进货价、成本价',
            'original_cost' => '原价',
            'after_discount_cost' => '会员价',
        ];
    }
}
