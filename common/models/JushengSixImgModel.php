<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jusheng_six_img".
 *
 * @property int $id
 * @property string $image_url
 */
class JushengSixImgModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jusheng_six_img';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image_url' => 'Image Url',
        ];
    }
}
