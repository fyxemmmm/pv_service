<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "channel".
 *
 * @property int $id
 * @property string $name 渠道名
 * @property string $channel_sign 渠道标识
 * @property string $url 链接
 * @property int $admin_id 渠道联系人
 * @property int $cooperation_type 1uv 2注册 3激活 4其他
 * @property string $qrcode 二维码
 * @property int $status 状态
 * @property string $app_url app下载链接
 * @property string $create_time 创建时间
 * @property string $del_time 删除时间
 * @property int $creater_id 创建者
 * @property int $template 模板id
 */
class ChannelModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'channel';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'url', 'cooperation_type', 'qrcode', 'create_time'], 'required'],
            [['admin_id', 'cooperation_type', 'status', 'creater_id', 'template'], 'integer'],
            [['create_time', 'del_time'], 'safe'],
            [['name'], 'string', 'max' => 25],
            [['channel_sign'], 'string', 'max' => 32],
            [['url'], 'string', 'max' => 150],
            [['qrcode', 'app_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '渠道名',
            'channel_sign' => '渠道标识',
            'url' => '链接',
            'admin_id' => '渠道联系人',
            'cooperation_type' => '1uv 2注册 3激活 4其他',
            'qrcode' => '二维码',
            'status' => '状态',
            'app_url' => 'app下载链接',
            'create_time' => '创建时间',
            'del_time' => '删除时间',
            'creater_id' => '创建者',
            'template' => '模板id',
        ];
    }
}
