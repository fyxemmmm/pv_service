<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_dc_desc".
 *
 * @property int $id
 * @property string $content
 * @property int $type
 * @property int $article_id
 */
class ArticleDcDescModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_dc_desc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['type', 'article_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'type' => 'Type',
            'article_id' => 'Article ID',
        ];
    }
}
