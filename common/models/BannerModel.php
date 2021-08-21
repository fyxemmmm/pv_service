<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banner".
 *
 * @property int $id
 * @property string $url banner图url地址
 * @property int $type 类型 0: 文章图文; 1: 音频视频; 2: h5
 * @property int $article_id
 * @property string $web_url h5 url
 * @property int $status
 * @property string $create_time
 * @property string $update_time
 */
class BannerModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'article_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['url', 'web_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'banner图url地址',
            'type' => '类型 0: 文章图文; 1: 音频视频; 2: h5',
            'article_id' => 'Article ID',
            'web_url' => 'h5 url',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
