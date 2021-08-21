<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_apply_price".
 *
 * @property int $id
 * @property int $user_id
 * @property string $apply_price 申请提现的金额
 * @property int $user_card_id
 * @property string $order_sign 订单号
 * @property string $fail_reason 被驳回的原因(提现失败的原因)
 * @property int $status 状态 0处理中，1提现成功，2被驳回
 * @property string $create_time
 * @property string $update_time
 */
class UserApplyPriceModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_apply_price';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'user_card_id', 'status'], 'integer'],
            [['apply_price'], 'number'],
            [['create_time', 'update_time'], 'safe'],
            [['order_sign'], 'string', 'max' => 255],
            [['fail_reason'], 'string', 'max' => 500],
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
            'apply_price' => '申请提现的金额',
            'user_card_id' => 'User Card ID',
            'order_sign' => '订单号',
            'fail_reason' => '被驳回的原因(提现失败的原因)',
            'status' => '状态 0处理中，1提现成功，2被驳回',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
