<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "week_news".
 *
 * @property int $id
 * @property string $title 标题
 * @property string $content 内容
 * @property int $sort
 */
class WeekNewsModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'week_news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['sort'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'content' => '内容',
            'sort' => 'Sort',
        ];
    }
}
