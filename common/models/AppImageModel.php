<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "app_image".
 *
 * @property string $key 键名
 * @property string $pic 图片
 */
class AppImageModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['key'], 'string', 'max' => 63],
            [['pic'], 'string', 'max' => 127],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => '键名',
            'pic' => '图片',
        ];
    }
}
