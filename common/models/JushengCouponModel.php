<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jusheng_coupon".
 *
 * @property int $id
 * @property string $coupon_code 第三方券码（第三方用于定位一张券的ID）
 * @property string $user_id 第三方用户ID
 * @property string $supplier_coupon_code 供应商券编码（巨省端约定的规格编码）
 * @property string $coupon_status 	0-未使用 1-已使用 2-已过期（预留）;3-使用中 99-劵不存在
 * @property int $type 1是视频周劵， 2是10元话费 ，3是5元话费
 * @property string $create_time
 * @property string $start_time 券开始时间
 * @property string $end_time 劵结束时间
 */
class JushengCouponModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jusheng_coupon';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['create_time', 'start_time', 'end_time'], 'safe'],
            [['coupon_code', 'user_id', 'supplier_coupon_code', 'coupon_status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coupon_code' => '第三方券码（第三方用于定位一张券的ID）',
            'user_id' => '第三方用户ID',
            'supplier_coupon_code' => '供应商券编码（巨省端约定的规格编码）',
            'coupon_status' => '	0-未使用 1-已使用 2-已过期（预留）;3-使用中 99-劵不存在',
            'type' => '1是视频周劵， 2是10元话费 ，3是5元话费',
            'create_time' => 'Create Time',
            'start_time' => '券开始时间',
            'end_time' => '劵结束时间',
        ];
    }
}
