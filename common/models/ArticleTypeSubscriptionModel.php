<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_type_subscription".
 *
 * @property int $id
 * @property int $user_id
 * @property int $article_type_id
 */
class ArticleTypeSubscriptionModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_type_subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'article_type_id'], 'integer'],
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
            'article_type_id' => 'Article Type ID',
        ];
    }
}
