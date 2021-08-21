<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_attendance".
 *
 * @property int $id
 * @property int $user_id
 * @property string $attendance_time 签到时间
 * @property int $continue_days 连续签到的天数
 * @property int $type 签到类型 0默认每日任务
 */
class UserAttendanceModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_attendance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'continue_days', 'type'], 'integer'],
            [['attendance_time'], 'safe'],
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
            'attendance_time' => '签到时间',
            'continue_days' => '连续签到的天数',
            'type' => '签到类型 0默认每日任务',
        ];
    }
}
