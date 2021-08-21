<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "focus".
 *
 * @property int $id
 * @property int $user_id
 * @property int $focus_user_id
 * @property int $read 是否已经读，1代表已读
 */
class FocusModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'focus';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'focus_user_id', 'read'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'focus_user_id' => 'Focus User ID',
            'read' => '是否已经读，1代表已读',
        ];
    }
}
