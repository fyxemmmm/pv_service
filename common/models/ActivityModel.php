<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity".
 *
 * @property int $id
 * @property string $title 活动标题
 * @property string $location 地址
 * @property string $price 价格
 * @property int $user_id 用户的id
 * @property string $preview_image 图片
 * @property string $activity_time 活动时间
 * @property string $activity_time_end 活动时间/结束
 * @property string $registration_deadline 报名截止时间
 * @property int $is_free 是否免费，1代表免费
 * @property string $content
 * @property int $is_end 是否已结束
 */
class ActivityModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price'], 'number'],
            [['user_id'], 'required'],
            [['user_id', 'is_free', 'is_end'], 'integer'],
            [['activity_time', 'activity_time_end', 'registration_deadline'], 'safe'],
            [['content'], 'string'],
            [['title', 'location', 'preview_image'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '活动标题',
            'location' => '地址',
            'price' => '价格',
            'user_id' => '用户的id',
            'preview_image' => '图片',
            'activity_time' => '活动时间',
            'activity_time_end' => '活动时间/结束',
            'registration_deadline' => '报名截止时间',
            'is_free' => '是否免费，1代表免费',
            'content' => 'Content',
            'is_end' => '是否已结束',
        ];
    }
}
