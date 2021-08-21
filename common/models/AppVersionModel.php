<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "app_version".
 *
 * @property int $id
 * @property string $name 版本名称
 * @property int $os 系统1android,2ios,3web,4其他
 * @property string $channel 渠道名
 * @property int $status 状态
 * @property int $auditing 1审核,0未审核
 * @property string $version 版本
 * @property string $app_url 下载地址
 * @property int $force 是否强制更新
 */
class AppVersionModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_version';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['os', 'status', 'auditing', 'force'], 'integer'],
            [['name', 'channel', 'version'], 'string', 'max' => 25],
            [['app_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '版本名称',
            'os' => '系统1android,2ios,3web,4其他',
            'channel' => '渠道名',
            'status' => '状态',
            'auditing' => '1审核,0未审核',
            'version' => '版本',
            'app_url' => '下载地址',
            'force' => '是否强制更新',
        ];
    }
}
