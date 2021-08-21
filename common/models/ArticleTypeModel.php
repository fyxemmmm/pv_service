<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_type".
 *
 * @property int $at_id
 * @property string $at_name 类型名称
 * @property string $topic 专题名称
 * @property string $topic_des 专题描述
 * @property int $subscription_num 订阅数
 * @property string $image 专题图片
 * @property int $weight 排序权重
 * @property int $is_dc 是否是贷超类型 0默认不是
 * @property int $is_del 删除状态
 */
class ArticleTypeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subscription_num', 'weight', 'is_dc', 'is_del'], 'integer'],
            [['at_name'], 'string', 'max' => 127],
            [['topic', 'topic_des', 'image'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'at_id' => 'At ID',
            'at_name' => '类型名称',
            'topic' => '专题名称',
            'topic_des' => '专题描述',
            'subscription_num' => '订阅数',
            'image' => '专题图片',
            'weight' => '排序权重',
            'is_dc' => '是否是贷超类型 0默认不是',
            'is_del' => '删除状态',
        ];
    }
}
