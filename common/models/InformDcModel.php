<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "inform_dc".
 *
 * @property int $id
 * @property string $title
 * @property string $describe
 * @property string $info
 * @property string $image_url
 * @property string $create_time
 */
class InformDcModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inform_dc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['info'], 'string'],
            [['create_time'], 'safe'],
            [['title', 'describe', 'image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'describe' => 'Describe',
            'info' => 'Info',
            'image_url' => 'Image Url',
            'create_time' => 'Create Time',
        ];
    }
}
