<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "message_setting".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $comment_reply 评论或回复了你 1代表从不提醒 0是任何人
 * @property int $focus 关注了你 1代表从不提醒 0是任何人
 */
class MessageSettingModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'comment_reply', 'focus'], 'integer'],
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
            'comment_reply' => '评论或回复了你 1代表从不提醒 0是任何人',
            'focus' => '关注了你 1代表从不提醒 0是任何人',
        ];
    }
}
