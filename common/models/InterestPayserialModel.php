<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "interest_payserial".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $card_id 用户id
 * @property string $y_user_id YHOUSE用户id
 * @property string $y_order_number YHOUSE订单id
 * @property int $days 天数
 * @property string $create_time 创建时间
 * @property string $finish_time 完成时间
 * @property string $cancel_time 连续包月取消时间
 */
class InterestPayserialModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'interest_payserial';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'card_id', 'days'], 'integer'],
            [['create_time', 'finish_time', 'cancel_time'], 'safe'],
            [['y_user_id'], 'string', 'max' => 63],
            [['y_order_number'], 'string', 'max' => 31],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'card_id' => '用户id',
            'y_user_id' => 'YHOUSE用户id',
            'y_order_number' => 'YHOUSE订单id',
            'days' => '天数',
            'create_time' => '创建时间',
            'finish_time' => '完成时间',
            'cancel_time' => '连续包月取消时间',
        ];
    }
}
