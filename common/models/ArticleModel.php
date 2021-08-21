<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property int $type 类型id
 * @property string $origin 来源
 * @property string $author 作者
 * @property string $title 标题
 * @property string $desc 描述
 * @property int $premium 是否精选 1代表是
 * @property string $profile 简介
 * @property string $content 内容
 * @property string $preview_image 图片
 * @property int $comment_num 评论数
 * @property int $like_num 点赞数
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property int $creater 创建者，用户id
 * @property int $admin_id 管理员id
 * @property int $is_dc 是否是贷超类型 0代表不是 1是
 * @property int $status 状态
 */
class ArticleModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'premium', 'comment_num', 'like_num', 'creater', 'admin_id', 'is_dc', 'status'], 'integer'],
            [['content'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['origin', 'author'], 'string', 'max' => 25],
            [['title'], 'string', 'max' => 50],
            [['desc'], 'string', 'max' => 150],
            [['profile', 'preview_image'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型id',
            'origin' => '来源',
            'author' => '作者',
            'title' => '标题',
            'desc' => '描述',
            'premium' => '是否精选 1代表是',
            'profile' => '简介',
            'content' => '内容',
            'preview_image' => '图片',
            'comment_num' => '评论数',
            'like_num' => '点赞数',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'creater' => '创建者，用户id',
            'admin_id' => '管理员id',
            'is_dc' => '是否是贷超类型 0代表不是 1是',
            'status' => '状态',
        ];
    }
}
