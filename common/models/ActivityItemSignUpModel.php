<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_item_sign_up".
 *
 * @property int $id
 * @property int $activity_id 活动id
 * @property int $activity_item_id 活动项目id
 * @property int $user_id 用户id
 * @property string $user_name 报名者姓名
 * @property string $mobile 报名者手机号
 * @property string $price 费用
 * @property string $create_time 报名时间
 */
class ActivityItemSignUpModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity_item_sign_up';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'activity_item_id', 'user_id'], 'integer'],
            [['price'], 'number'],
            [['create_time'], 'safe'],
            [['user_name', 'mobile'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => '活动id',
            'activity_item_id' => '活动项目id',
            'user_id' => '用户id',
            'user_name' => '报名者姓名',
            'mobile' => '报名者手机号',
            'price' => '费用',
            'create_time' => '报名时间',
        ];
    }
}
