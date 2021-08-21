<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_background".
 *
 * @property int $id
 * @property string $img_url 图片地址
 * @property int $sort_num 排序
 */
class BusinessBackgroundModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business_background';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_num'], 'integer'],
            [['img_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'img_url' => '图片地址',
            'sort_num' => '排序',
        ];
    }
}
