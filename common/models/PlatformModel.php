<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "platform".
 *
 * @property int $id
 * @property string $name 渠道名
 * @property string $apk_url 阿里云存放apk文件的地址
 */
class PlatformModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'platform';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'apk_url'], 'string', 'max' => 255],
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
            'apk_url' => '阿里云存放apk文件的地址',
        ];
    }
}
