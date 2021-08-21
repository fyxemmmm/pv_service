<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_type_insert".
 *
 * @property int $id
 * @property int $article_type_id
 * @property int $offset 插入到文章的offset的索引
 */
class ArticleTypeInsertModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_type_insert';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_type_id', 'offset'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_type_id' => 'Article Type ID',
            'offset' => '插入到文章的offset的索引',
        ];
    }
}
