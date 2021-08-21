<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "im_message_upvote".
 *
 * @property int $id
 * @property string $group_id 群组id
 * @property string $message_id 消息id
 * @property string $upvote_user_id 赞同者
 * @property string $create_time 创建时间
 * @property int $status 状态
 */
class ImMessageUpvoteModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'im_message_upvote';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'message_id', 'status'], 'integer'],
            [['upvote_user_id'], 'string'],
            [['create_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => '群组id',
            'message_id' => '消息id',
            'upvote_user_id' => '赞同者',
            'create_time' => '创建时间',
            'status' => '状态',
        ];
    }
}
