<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin".
 *
 * @property int $id
 * @property string $name 登录名(手机号)
 * @property string $realname 真实姓名
 * @property string $password 密码
 * @property string $access_token 口令
 * @property string $avatar_image 头像
 * @property int $creater 创建者
 * @property string $last_login_time 最后的登录时间
 * @property string $last_login_ip 最后的登录ip
 * @property string $register_time 注册时间
 * @property string $register_ip 注册ip
 * @property int $status 状态(正常/禁用)
 * @property int $type 1管理员,2运营,3渠道,4商家
 * @property int $login_time 登录次数
 */
class AdminModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['creater', 'status', 'type', 'login_time'], 'integer'],
            [['last_login_time', 'register_time'], 'safe'],
            [['name'], 'string', 'max' => 25],
            [['realname', 'avatar_image'], 'string', 'max' => 255],
            [['password'], 'string', 'max' => 60],
            [['access_token'], 'string', 'max' => 32],
            [['last_login_ip', 'register_ip'], 'string', 'max' => 15],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '登录名(手机号)',
            'realname' => '真实姓名',
            'password' => '密码',
            'access_token' => '口令',
            'avatar_image' => '头像',
            'creater' => '创建者',
            'last_login_time' => '最后的登录时间',
            'last_login_ip' => '最后的登录ip',
            'register_time' => '注册时间',
            'register_ip' => '注册ip',
            'status' => '状态(正常/禁用)',
            'type' => '1管理员,2运营,3渠道,4商家',
            'login_time' => '登录次数',
        ];
    }
}
