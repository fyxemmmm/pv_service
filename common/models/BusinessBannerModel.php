<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_banner".
 *
 * @property int $id
 * @property string $img_url 图片地址
 * @property string $web_url h5地址
 * @property string $share_url h5分享地址
 * @property string $share_title 分享标题
 * @property string $share_desc 分享描述
 */
class BusinessBannerModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business_banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['img_url', 'web_url', 'share_url', 'share_title', 'share_desc'], 'string', 'max' => 255],
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
            'web_url' => 'h5地址',
            'share_url' => 'h5分享地址',
            'share_title' => '分享标题',
            'share_desc' => '分享描述',
        ];
    }
}
