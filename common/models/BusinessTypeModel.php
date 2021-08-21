<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_type".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $logo 图片地址
 * @property string $index_logo 首页图片地址
 * @property string $info_logo 内页图片地址
 */
class BusinessTypeModel extends \yii\db\ActiveRecord
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
            [['logo', 'index_logo', 'info_logo'], 'string', 'max' => 255],
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
            'index_logo' => '首页图片地址',
            'info_logo' => '内页图片地址',
        ];
    }
}
