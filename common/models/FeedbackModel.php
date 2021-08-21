<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "feedback".
 *
 * @property int $id
 * @property string $user_name 联系人姓名
 * @property string $email 联系人邮箱
 * @property string $mobile 联系电话
 * @property string $content 内容
 * @property int $user_id 用户id
 * @property int $type 1反馈,2合作
 * @property int $feedback_type 1页面展示,2产品问题,3操作问题
 * @property string $create_time
 */
class FeedbackModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'feedback_type'], 'integer'],
            [['create_time'], 'safe'],
            [['user_name'], 'string', 'max' => 10],
            [['email'], 'string', 'max' => 30],
            [['mobile', 'content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_name' => '联系人姓名',
            'email' => '联系人邮箱',
            'mobile' => '联系电话',
            'content' => '内容',
            'user_id' => '用户id',
            'type' => '1反馈,2合作',
            'feedback_type' => '1页面展示,2产品问题,3操作问题',
            'create_time' => 'Create Time',
        ];
    }
}
