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
 * @property int $gender 性别 1男， 2女
 * @property int $city_id 地区id
 * @property string $wechat_token 微信openid
 * @property string $qq_token qq_openid
 * @property string $avatar_image 头像
 * @property string $register_time 注册时间
 * @property string $last_login_time 最后一次登录时间
 * @property int $register_ip 注册ip
 * @property int $last_login_ip 最后一次登录ip
 * @property int $status 状态 0 禁用，1 正常， 2 发布仅自己可见
 * @property string $device_id 设备号
 * @property int $os 1a,2i,3w,4o
 * @property int $focus_num 关注其他人的数量
 * @property int $focused_num 被关注的人的数量
 * @property int $login_time 登录次数
 * @property int $active 激活
 * @property string $active_time 激活时间
 * @property int $conceal_hide 隐私,1显示 0隐藏
 * @property string $cause_of_violation 违规原因
 * @property string $huanxin_uuid 环信uuid
 * @property string $huanxin_type 环信用户类型
 * @property int $huanxin_created 环信创建时间
 * @property int $huanxin_modified 环信更新时间
 * @property string $huanxin_username 环信用户名
 * @property string $huanxin_password 环信密码
 * @property int $huanxin_activated 环信激活状态
 * @property string $huanxin_nickname 环信昵称
 * @property int $user_level 贷超等级 白银0 黄金1 钻石2
 * @property string $total_income 总收入
 * @property string $available_money 可用余额(可提现)
 * @property string $platform 平台
 */
class UserModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['gender', 'city_id', 'register_ip', 'last_login_ip', 'status', 'os', 'focus_num', 'focused_num', 'login_time', 'active', 'conceal_hide', 'huanxin_created', 'huanxin_modified', 'huanxin_activated', 'user_level'], 'integer'],
            [['register_time', 'last_login_time', 'active_time'], 'safe'],
            [['total_income', 'available_money'], 'number'],
            [['mobile'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 60],
            [['access_token'], 'string', 'max' => 32],
            [['nick_name'], 'string', 'max' => 15],
            [['wechat_token', 'qq_token', 'avatar_image', 'cause_of_violation'], 'string', 'max' => 255],
            [['device_id', 'huanxin_password'], 'string', 'max' => 100],
            [['huanxin_uuid'], 'string', 'max' => 50],
            [['huanxin_type', 'huanxin_username', 'huanxin_nickname'], 'string', 'max' => 25],
            [['platform'], 'string', 'max' => 63],
            [['mobile'], 'unique'],
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
            'gender' => '性别 1男， 2女',
            'city_id' => '地区id',
            'wechat_token' => '微信openid',
            'qq_token' => 'qq_openid',
            'avatar_image' => '头像',
            'register_time' => '注册时间',
            'last_login_time' => '最后一次登录时间',
            'register_ip' => '注册ip',
            'last_login_ip' => '最后一次登录ip',
            'status' => '状态 0 禁用，1 正常， 2 发布仅自己可见',
            'device_id' => '设备号',
            'os' => '1a,2i,3w,4o',
            'focus_num' => '关注其他人的数量',
            'focused_num' => '被关注的人的数量',
            'login_time' => '登录次数',
            'active' => '激活',
            'active_time' => '激活时间',
            'conceal_hide' => '隐私,1显示 0隐藏',
            'cause_of_violation' => '违规原因',
            'huanxin_uuid' => '环信uuid',
            'huanxin_type' => '环信用户类型',
            'huanxin_created' => '环信创建时间',
            'huanxin_modified' => '环信更新时间',
            'huanxin_username' => '环信用户名',
            'huanxin_password' => '环信密码',
            'huanxin_activated' => '环信激活状态',
            'huanxin_nickname' => '环信昵称',
            'user_level' => '贷超等级 白银0 黄金1 钻石2',
            'total_income' => '总收入',
            'available_money' => '可用余额(可提现)',
            'platform' => '平台',
        ];
    }
}
