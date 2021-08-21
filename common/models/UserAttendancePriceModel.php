<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_attendance_price".
 *
 * @property int $id
 * @property string $attendance_price 指定奖励金额
 */
class UserAttendancePriceModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_attendance_price';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attendance_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'attendance_price' => '指定奖励金额',
        ];
    }
}
