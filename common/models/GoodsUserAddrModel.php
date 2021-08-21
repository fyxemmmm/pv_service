<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_user_addr".
 *
 * @property int $id
 * @property int $user_id
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $addr 用户地址
 * @property string $user_name
 * @property string $mobile
 * @property int $is_default 是否是默认的地址
 * @property string $create_time
 */
class GoodsUserAddrModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods_user_addr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'is_default'], 'integer'],
            [['is_default'], 'required'],
            [['create_time'], 'safe'],
            [['province', 'city', 'district', 'addr', 'user_name', 'mobile'], 'string', 'max' => 255],
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
            'province' => 'Province',
            'city' => 'City',
            'district' => 'District',
            'addr' => '用户地址',
            'user_name' => 'User Name',
            'mobile' => 'Mobile',
            'is_default' => '是否是默认的地址',
            'create_time' => 'Create Time',
        ];
    }
}
