<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_report".
 *
 * @property int $id
 * @property int $user_id 举报者 uid
 * @property int $article_id 被举报的文章id
 * @property string $content 举报的原因
 * @property string $create_time
 */
class ArticleReportModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'article_id'], 'integer'],
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
            'user_id' => '举报者 uid',
            'article_id' => '被举报的文章id',
            'content' => '举报的原因',
            'create_time' => 'Create Time',
        ];
    }
}
