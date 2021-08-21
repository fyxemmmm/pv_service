<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_user_share".
 *
 * @property int $id
 * @property int $user_id
 * @property int $goods_id
 * @property string $create_time
 */
class GoodsUserShareModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_user_share';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'goods_id'], 'integer'],
            [['create_time'], 'safe'],
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
            'goods_id' => 'Goods ID',
            'create_time' => 'Create Time',
        ];
    }
}
