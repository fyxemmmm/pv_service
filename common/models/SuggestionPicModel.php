<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "suggestion_pic".
 *
 * @property int $id
 * @property int $suggestion_id
 * @property string $img_url 图片地址
 */
class SuggestionPicModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'suggestion_pic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['suggestion_id'], 'integer'],
            [['img_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'suggestion_id' => 'Suggestion ID',
            'img_url' => '图片地址',
        ];
    }
}
