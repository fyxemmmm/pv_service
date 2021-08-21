<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "coupon".
 *
 * @property int $id
 * @property string $coupon_code 代金券码
 * @property int $type_id 分类ID
 * @property int $overdue 过期时长
 * @property int $status 0关闭 1开启
 * @property string $name 代金券名
 * @property string $description 代金券描述
 * @property string $amount 抵扣金额
 * @property string $limit_amount 最低可用价格
 * @property int $admin_id 管理员
 * @property string $create_time 添加时间
 * @property string $update_time 修改时间
 */
class CouponModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupon';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'overdue', 'status', 'admin_id'], 'integer'],
            [['amount', 'limit_amount'], 'number'],
            [['admin_id'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['coupon_code', 'name', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coupon_code' => '代金券码',
            'type_id' => '分类ID',
            'overdue' => '过期时长',
            'status' => '0关闭 1开启',
            'name' => '代金券名',
            'description' => '代金券描述',
            'amount' => '抵扣金额',
            'limit_amount' => '最低可用价格',
            'admin_id' => '管理员',
            'create_time' => '添加时间',
            'update_time' => '修改时间',
        ];
    }
}
