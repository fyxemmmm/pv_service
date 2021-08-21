<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_banner".
 *
 * @property int $id
 * @property int $jmb_id
 * @property string $image_url
 */
class JmbBannerModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jmb_id'], 'integer'],
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
            'jmb_id' => 'Jmb ID',
            'image_url' => 'Image Url',
        ];
    }
}
