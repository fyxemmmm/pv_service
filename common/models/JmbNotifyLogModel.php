<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_notify_log".
 *
 * @property int $id
 * @property string $content content
 * @property int $status 处理状态
 * @property string $create_time 创建时间
 */
class JmbNotifyLogModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_notify_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['create_time'], 'safe'],
            [['content'], 'string', 'max' => 511],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'content',
            'status' => '处理状态',
            'create_time' => '创建时间',
        ];
    }
}
