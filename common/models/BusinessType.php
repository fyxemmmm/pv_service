<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_type".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $logo 图片地址
 */
class BusinessType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 63],
            [['logo'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'logo' => '图片地址',
        ];
    }
}
