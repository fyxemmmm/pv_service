<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "radio_shaft".
 *
 * @property int $id
 * @property int $radio_id
 * @property int $at 时间点
 * @property string $title
 * @property string $image 图片
 * @property string $content
 * @property string $quote_href 外链
 */
class RadioShaftModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radio_shaft';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['radio_id', 'at'], 'integer'],
            [['content'], 'string'],
            [['title', 'image', 'quote_href'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'radio_id' => 'Radio ID',
            'at' => '时间点',
            'title' => 'Title',
            'image' => '图片',
            'content' => 'Content',
            'quote_href' => '外链',
        ];
    }
}
