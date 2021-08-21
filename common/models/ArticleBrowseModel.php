<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "article_browse".
 *
 * @property int $id
 * @property int $ip ip地址
 * @property int $article_id
 * @property int $user_id
 * @property string $create_time
 */
class ArticleBrowseModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article_browse';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ip', 'article_id', 'user_id'], 'integer'],
            [['create_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'ip地址',
            'article_id' => 'Article ID',
            'user_id' => 'User ID',
            'create_time' => 'Create Time',
        ];
    }
}
