<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jusheng_log".
 *
 * @property int $id
 * @property string $content
 * @property string $action_name
 * @property string $create_time
 * @property string $ip
 */
class JushengLogModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jusheng_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['create_time'], 'safe'],
            [['action_name', 'ip'], 'string', 'max' => 255],
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
            'action_name' => 'Action Name',
            'create_time' => 'Create Time',
            'ip' => 'Ip',
        ];
    }
}
