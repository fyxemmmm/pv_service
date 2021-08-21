<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_category".
 *
 * @property int $id
 * @property int $pid 分类层级 0顶层分类
 * @property string $name 分类名
 * @property string $image_url logo图片的url
 */
class JmbCategoryModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pid'], 'integer'],
            [['name', 'image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => '分类层级 0顶层分类',
            'name' => '分类名',
            'image_url' => 'logo图片的url',
        ];
    }
}
