<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_xmob".
 *
 * @property int $id
 * @property string $mobile 用户
 * @property string $mob_cid 点击数据ID
 * @property string $plid 广告单元ID
 * @property string $regist 注册
 * @property int $active 激活
 * @property string $g_buy 商品购买id逗号拼接
 * @property string $q_buy 权益购买id逗号拼接
 * @property string $type 对接类型
 * @property string $create_time 创建时间
 */
class UserXmobModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_xmob';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active'], 'integer'],
            [['create_time'], 'safe'],
            [['mobile', 'mob_cid', 'plid', 'regist', 'g_buy', 'q_buy', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => '用户',
            'mob_cid' => '点击数据ID',
            'plid' => '广告单元ID',
            'regist' => '注册',
            'active' => '激活',
            'g_buy' => '商品购买id逗号拼接',
            'q_buy' => '权益购买id逗号拼接',
            'type' => '对接类型',
            'create_time' => '创建时间',
        ];
    }
}
