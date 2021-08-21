<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "coupon_banner".
 *
 * @property int $id
 * @property string $img_url 图片地址
 * @property string $web_url h5地址
 * @property string $share_url h5分享地址
 * @property int $type 类型1大图2小图
 * @property int $status 0不弹出1弹出
 * @property string $title 标题
 * @property string $share_title 分享标题
 * @property string $share_desc 分享内容
 */
class CouponBannerModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupon_banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'status'], 'integer'],
            [['img_url', 'web_url', 'share_url', 'title', 'share_title', 'share_desc'], 'string', 'max' => 255],
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
            'type' => '类型1大图2小图',
            'status' => '0不弹出1弹出',
            'title' => '标题',
            'share_title' => '分享标题',
            'share_desc' => '分享内容',
        ];
    }
}
