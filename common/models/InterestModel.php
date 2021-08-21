<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "interest".
 *
 * @property int $id
 * @property int $t_id 类型id
 * @property string $name 名称
 * @property string $pic 图片
 */
class InterestModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'interest';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['t_id'], 'integer'],
            [['name'], 'string', 'max' => 127],
            [['pic'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            't_id' => '类型id',
            'name' => '名称',
            'pic' => '图片',
        ];
    }
}
