<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "app_switch".
 *
 * @property string $key 键
 * @property string $value 渠道名
 */
class AppSwitchModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_switch';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['key'], 'string', 'max' => 63],
            [['value'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => '键',
            'value' => '渠道名',
        ];
    }
}
