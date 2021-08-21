<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_collect".
 *
 * @property int $id
 * @property int $user_id
 * @property int $article_id
 */
class ArticleCollectModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_collect';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'article_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'article_id' => 'Article ID',
        ];
    }
}
