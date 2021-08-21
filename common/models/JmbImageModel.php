<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_image".
 *
 * @property int $id
 * @property string $image_url
 * @property int $type 1banner图  2分类上的广告图
 * @property int $jmb_id
 * @property int $use_out_link 是否使用外部链接
 * @property string $out_link_url 外部链接url
 */
class JmbImageModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'jmb_id', 'use_out_link'], 'integer'],
            [['image_url'], 'string', 'max' => 255],
            [['out_link_url'], 'string', 'max' => 500],
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
            'type' => '1banner图  2分类上的广告图',
            'jmb_id' => 'Jmb ID',
            'use_out_link' => '是否使用外部链接',
            'out_link_url' => '外部链接url',
        ];
    }
}
