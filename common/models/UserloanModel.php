<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id ID
 * @property string $mobile 手机号
 * @property string $password 密码
 * @property string $access_token 通行证
 * @property string $nick_name 昵称
 * @property string $avatar_image 头像
 * @property string $register_time 注册时间
 * @property string $last_login_time 最后一次登录时间
 * @property int $register_ip 注册ip
 * @property int $last_login_ip 最后一次登录ip
 * @property int $status 状态
 * @property int $active 激活
 * @property string $active_time 激活时间
 * @property int $channel_id 渠道
 * @property int $package_id 马甲包
 * @property string $device_id 设备号
 * @property int $os 1a,2i,3w,4o
 * @property int $login_time 登录次数
 * @property string $platform 平台
 */
class UserloanModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
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
            [['register_time', 'last_login_time', 'active_time'], 'safe'],
            [['register_ip', 'last_login_ip', 'status', 'active', 'channel_id', 'package_id', 'os', 'login_time'], 'integer'],
            [['mobile'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 60],
            [['access_token'], 'string', 'max' => 32],
            [['nick_name'], 'string', 'max' => 15],
            [['avatar_image'], 'string', 'max' => 50],
            [['device_id'], 'string', 'max' => 100],
            [['platform'], 'string', 'max' => 63],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => '手机号',
            'password' => '密码',
            'access_token' => '通行证',
            'nick_name' => '昵称',
            'avatar_image' => '头像',
            'register_time' => '注册时间',
            'last_login_time' => '最后一次登录时间',
            'register_ip' => '注册ip',
            'last_login_ip' => '最后一次登录ip',
            'status' => '状态',
            'active' => '激活',
            'active_time' => '激活时间',
            'channel_id' => '渠道',
            'package_id' => '马甲包',
            'device_id' => '设备号',
            'os' => '1a,2i,3w,4o',
            'login_time' => '登录次数',
            'platform' => '平台',
        ];
    }
}
