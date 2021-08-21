<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "radio".
 *
 * @property int $id
 * @property string $radio_url 音频的链接地址
 * @property string $size 音频的大小 MB
 * @property int $type 类型id
 * @property string $origin 来源
 * @property string $title 标题
 * @property string $desc 描述
 * @property string $content 内容
 * @property string $preview_image 图片
 * @property int $comment_num 评论数
 * @property int $like_num 点赞数
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property int $admin_id 管理员id
 * @property int $status 状态
 */
class RadioModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['size'], 'number'],
            [['type', 'comment_num', 'like_num', 'admin_id', 'status'], 'integer'],
            [['content'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['radio_url', 'preview_image'], 'string', 'max' => 255],
            [['origin'], 'string', 'max' => 25],
            [['title'], 'string', 'max' => 50],
            [['desc'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'radio_url' => '音频的链接地址',
            'size' => '音频的大小 MB',
            'type' => '类型id',
            'origin' => '来源',
            'title' => '标题',
            'desc' => '描述',
            'content' => '内容',
            'preview_image' => '图片',
            'comment_num' => '评论数',
            'like_num' => '点赞数',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'admin_id' => '管理员id',
            'status' => '状态',
        ];
    }
}
