<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "suggestion".
 *
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property string $mobile
 * @property string $email
 * @property string $create_time
 */
class SuggestionModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'suggestion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['content', 'mobile', 'email'], 'string', 'max' => 255],
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
            'content' => 'Content',
            'mobile' => 'Mobile',
            'email' => 'Email',
            'create_time' => 'Create Time',
        ];
    }
}
