<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_hot_category".
 *
 * @property int $id
 * @property int $jmb_category_id 加盟宝非0分类id
 * @property string $name 标题
 * @property string $desc 描述
 * @property string $image_url 图片
 */
class JmbHotCategoryModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_hot_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jmb_category_id'], 'integer'],
            [['name', 'desc', 'image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jmb_category_id' => '加盟宝非0分类id',
            'name' => '标题',
            'desc' => '描述',
            'image_url' => '图片',
        ];
    }
}
