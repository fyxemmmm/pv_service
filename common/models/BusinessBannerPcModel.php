<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_banner_pc".
 *
 * @property int $id
 * @property string $img_url 图片地址
 * @property int $type 类型 0:创业邦 1:外联
 * @property int $data_id 数据id
 * @property string $web_url h5地址
 */
class BusinessBannerPcModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business_banner_pc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'data_id'], 'integer'],
            [['img_url', 'web_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'img_url' => '图片地址',
            'type' => '类型 0:创业邦 1:外联',
            'data_id' => '数据id',
            'web_url' => 'h5地址',
        ];
    }
}
