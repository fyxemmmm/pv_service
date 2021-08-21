<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "make_money_banner".
 *
 * @property int $id
 * @property int $type 类型 0:创业邦列表
 * @property string $pic 图片
 */
class MakeMoneyBannerModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'make_money_banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['pic'], 'string', 'max' => 127],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型 0:创业邦列表',
            'pic' => '图片',
        ];
    }
}
