<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "coupon_user".
 *
 * @property int $id
 * @property string $coupon_code 优惠券码
 * @property string $user_id 用户id
 * @property string $coupon_status 0-未使用 1-已使用 2-已过期（预留）;3-使用中 99-劵不存在
 * @property string $start_time 券领取时间
 * @property string $end_time 劵结束时间
 */
class CouponUserModel extends \yii\db\ActiveRecord
{
    public $amount;
    public $limit_amount;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupon_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time'], 'safe'],
            [['coupon_code', 'user_id', 'coupon_status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coupon_code' => '优惠券码',
            'user_id' => '用户id',
            'coupon_status' => '0-未使用 1-已使用 2-已过期（预留）;3-使用中 99-劵不存在',
            'start_time' => '券领取时间',
            'end_time' => '劵结束时间',
        ];
    }
}
