<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "interest_type".
 *
 * @property int $id
 * @property string $name 卡名
 * @property string $description 描述
 * @property string $pic 图片
 * @property int $info_view 详情显示
 */
class InterestTypeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'interest_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['info_view'], 'integer'],
            [['name'], 'string', 'max' => 127],
            [['description', 'pic'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '卡名',
            'description' => '描述',
            'pic' => '图片',
            'info_view' => '详情显示',
        ];
    }
}
