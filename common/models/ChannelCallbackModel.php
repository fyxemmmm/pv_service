<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "channel_callback".
 *
 * @property int $id
 * @property string $callback_url
 * @property string $imei
 * @property string $imeimd5
 * @property string $androidid
 * @property string $androididmd5
 * @property string $oaid
 * @property int $status
 * @property string $create_time
 * @property string $update_time
 * @property string $type 哪个渠道进来的
 * @property string $ad_id 广告id
 * @property string $ad_name 广告名称
 * @property string $user_mobile 用户手机
 */
class ChannelCallbackModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'channel_callback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['callback_url', 'imei', 'imeimd5', 'androidid', 'androididmd5', 'oaid', 'type', 'ad_id', 'ad_name', 'user_mobile'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'callback_url' => 'Callback Url',
            'imei' => 'Imei',
            'imeimd5' => 'Imeimd5',
            'androidid' => 'Androidid',
            'androididmd5' => 'Androididmd5',
            'oaid' => 'Oaid',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'type' => '哪个渠道进来的',
            'ad_id' => '广告id',
            'ad_name' => '广告名称',
            'user_mobile' => '用户手机',
        ];
    }
}
