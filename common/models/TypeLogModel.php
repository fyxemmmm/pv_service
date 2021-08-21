<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "type_log".
 *
 * @property int $id
 * @property string $content
 * @property int $type
 * @property string $create_time
 */
class TypeLogModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'type_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['type'], 'integer'],
            [['create_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }
}
