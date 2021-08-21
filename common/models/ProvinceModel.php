<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "province".
 *
 * @property int $id
 * @property string $name
 * @property int $pid
 */
class ProvinceModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'province';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'pid'], 'integer'],
            [['name'], 'string', 'max' => 127],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'pid' => 'Pid',
        ];
    }
}
