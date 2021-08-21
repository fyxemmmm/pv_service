<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_type".
 *
 * @property int $id
 * @property string $name 类型名称
 * @property string $image_url 类型图
 * @property int $order 分类排序
 */
class GoodsTypeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order'], 'integer'],
            [['name', 'image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '类型名称',
            'image_url' => '类型图',
            'order' => '分类排序',
        ];
    }
}
