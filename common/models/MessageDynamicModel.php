<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "message_dynamic".
 *
 * @property int $id
 * @property int $user_id 评论者id
 * @property int $accept_user_id 被评论的用户id
 * @property int $type 类型，1对文章评论，2对评论的回复
 * @property int $article_comment_id 文章或者评论的id，根据type决定
 * @property string $content 内容
 * @property int $read 是否已读，1代表已读
 * @property string $create_time
 */
class MessageDynamicModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message_dynamic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'accept_user_id', 'type', 'article_comment_id', 'read'], 'integer'],
            [['create_time'], 'safe'],
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
            'user_id' => '评论者id',
            'accept_user_id' => '被评论的用户id',
            'type' => '类型，1对文章评论，2对评论的回复',
            'article_comment_id' => '文章或者评论的id，根据type决定',
            'content' => '内容',
            'read' => '是否已读，1代表已读',
            'create_time' => 'Create Time',
        ];
    }
}
