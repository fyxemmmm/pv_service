<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "make_money_type".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $pic 图片
 * @property string $lk_pic 镂空pic
 */
class MakeMoneyTypeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'make_money_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'pic', 'lk_pic'], 'string', 'max' => 127],
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
            'pic' => '图片',
            'lk_pic' => '镂空pic',
        ];
    }
}
