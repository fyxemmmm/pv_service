<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_cart".
 *
 * @property int $id
 * @property int $u_id 用户id
 * @property array $data 商品信息
 * @property string $update_time 更新时间
 */
class GoodsCartModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_cart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_id'], 'integer'],
            [['data', 'update_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'u_id' => '用户id',
            'data' => '商品信息',
            'update_time' => '更新时间',
        ];
    }
}
