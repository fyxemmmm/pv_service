<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods".
 *
 * @property int $id
 * @property string $name 商品名称
 * @property int $goods_type_id 类型id
 * @property string $image 商品图片
 * @property string $specification_desc 规格描述
 * @property string $service 提供什么服务
 * @property string $goods_detail 商品说明
 * @property string $detail_content 商品的详情
 * @property string $good_tax 商品税利率 0.03
 * @property string $logistics_fee 所需邮费 15
 * @property string $discount 会员折扣/推广折扣 7.8
 * @property string $profitable_rate 盈利百分比 0.1
 * @property int $is_recommand 是否推荐 1是推荐
 * @property int $status 状态
 * @property string $main_specification_name 主规格名
 * @property string $second_specification_name 副规格名
 * @property int $creator_id 创建者id
 * @property string $create_time
 * @property string $update_time
 * @property string $top_time
 */
class GoodsModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_type_id', 'is_recommand', 'status', 'creator_id'], 'integer'],
            [['detail_content'], 'string'],
            [['good_tax', 'logistics_fee', 'profitable_rate'], 'number'],
            [['create_time', 'update_time', 'top_time'], 'safe'],
            [['name', 'image', 'specification_desc', 'service', 'goods_detail', 'main_specification_name', 'second_specification_name'], 'string', 'max' => 255],
            [['discount'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '商品名称',
            'goods_type_id' => '类型id',
            'image' => '商品图片',
            'specification_desc' => '规格描述',
            'service' => '提供什么服务',
            'goods_detail' => '商品说明',
            'detail_content' => '商品的详情',
            'good_tax' => '商品税利率 0.03',
            'logistics_fee' => '所需邮费 15',
            'discount' => '会员折扣/推广折扣 7.8',
            'profitable_rate' => '盈利百分比 0.1',
            'is_recommand' => '是否推荐 1是推荐',
            'status' => '状态',
            'main_specification_name' => '主规格名',
            'second_specification_name' => '副规格名',
            'creator_id' => '创建者id',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'top_time' => 'Top Time',
        ];
    }
}
