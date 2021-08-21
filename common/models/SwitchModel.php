<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "switch".
 *
 * @property int $id
 * @property int $type 类型 1文章、专题贷超
 * @property int $status 0不进行字段过滤，1进行字段过滤
 */
class SwitchModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'switch';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型 1文章、专题贷超',
            'status' => '0不进行字段过滤，1进行字段过滤',
        ];
    }
}
