<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "app_setting".
 *
 * @property int $id
 * @property string $webview_url app webview url
 * @property string $share_url 文章分享的url
 * @property string $work_url work_url
 * @property string $dc_url 贷超url
 * @property string $activity_url 活动url
 * @property string $bang_share_url 创业bang分享的url
 * @property string $bang_banner_share_url 创业bang的banner图分享的url
 * @property string $bang_share_title 创业bang分享title
 * @property string $week_news_logo 小报头部logo
 */
class AppSettingModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['webview_url', 'share_url', 'work_url', 'dc_url', 'activity_url', 'bang_share_url', 'bang_banner_share_url', 'bang_share_title', 'week_news_logo'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'webview_url' => 'app webview url',
            'share_url' => '文章分享的url',
            'work_url' => 'work_url',
            'dc_url' => '贷超url',
            'activity_url' => '活动url',
            'bang_share_url' => '创业bang分享的url',
            'bang_banner_share_url' => '创业bang的banner图分享的url',
            'bang_share_title' => '创业bang分享title',
            'week_news_logo' => '小报头部logo',
        ];
    }
}
