<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jusheng_vip".
 *
 * @property int $id
 * @property int $user_id 我方用户id
 * @property int $vip_type 1季度会员 2终生会员
 * @property int $jusheng_vip_id 巨省会员ID
 * @property string $expire_time 会员过期时间
 * @property string $create_time
 * @property string $update_time
 */
class JushengVipModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jusheng_vip';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'vip_type', 'jusheng_vip_id'], 'integer'],
            [['expire_time', 'create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '我方用户id',
            'vip_type' => '1季度会员 2终生会员',
            'jusheng_vip_id' => '巨省会员ID',
            'expire_time' => '会员过期时间',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
