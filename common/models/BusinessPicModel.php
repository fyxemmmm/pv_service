<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_pic".
 *
 * @property int $id
 * @property int $b_id 创业id
 * @property string $img_url 图片地址
 */
class BusinessPicModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business_pic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['b_id'], 'integer'],
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
            'b_id' => '创业id',
            'img_url' => '图片地址',
        ];
    }
}
