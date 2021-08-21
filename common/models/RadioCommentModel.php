<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "radio_comment".
 *
 * @property int $id
 * @property int $radio_id
 * @property int $user_id 发表评论的用户id
 * @property string $content 评论内容
 * @property int $pid
 * @property int $reply_pid 回复者的id
 * @property int $like_num 点赞数
 * @property int $child_count 子评论个数
 * @property string $create_time
 * @property string $update_time
 * @property int $del 删除
 * @property int $is_private 私有
 */
class RadioCommentModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radio_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['radio_id', 'user_id', 'pid', 'reply_pid', 'like_num', 'child_count', 'del', 'is_private'], 'integer'],
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
            'radio_id' => 'Radio ID',
            'user_id' => '发表评论的用户id',
            'content' => '评论内容',
            'pid' => 'Pid',
            'reply_pid' => '回复者的id',
            'like_num' => '点赞数',
            'child_count' => '子评论个数',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'del' => '删除',
            'is_private' => '私有',
        ];
    }
}
