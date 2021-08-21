<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_income".
 *
 * @property int $id
 * @property int $type 类型 0默认后台返现   1签到  2下家第一次下款返佣
 * @property int $user_id
 * @property string $order_sign 订单号
 * @property string $income 收入金额
 * @property string $create_time
 */
class UserIncomeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_income';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'user_id'], 'integer'],
            [['income'], 'number'],
            [['create_time'], 'safe'],
            [['order_sign'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型 0默认后台返现   1签到  2下家第一次下款返佣',
            'user_id' => 'User ID',
            'order_sign' => '订单号',
            'income' => '收入金额',
            'create_time' => 'Create Time',
        ];
    }
}
