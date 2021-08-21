<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_label".
 *
 * @property int $id
 * @property int $article_id
 * @property int $label_id
 */
class ArticleLabelModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_label';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_id', 'label_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_id' => 'Article ID',
            'label_id' => 'Label ID',
        ];
    }
}
