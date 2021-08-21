<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_comment".
 *
 * @property int $id
 * @property int $jmb_id
 * @property int $user_id 发表评论的用户id
 * @property string $content 评论内容
 * @property int $pid
 * @property int $reply_pid 回复者的id
 * @property int $like_num 点赞数
 * @property string $create_time
 * @property string $update_time
 */
class JmbCommentModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jmb_id', 'user_id', 'pid', 'reply_pid', 'like_num'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jmb_id' => 'Jmb ID',
            'user_id' => '发表评论的用户id',
            'content' => '评论内容',
            'pid' => 'Pid',
            'reply_pid' => '回复者的id',
            'like_num' => '点赞数',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
