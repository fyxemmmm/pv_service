<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_click".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $goods_id 商品ID
 * @property int $create_time 浏览时间
 */
class GoodsClickModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_click';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'goods_id', 'create_time'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'goods_id' => '商品ID',
            'create_time' => '浏览时间',
        ];
    }
}
