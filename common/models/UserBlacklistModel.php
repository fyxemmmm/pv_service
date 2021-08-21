<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_blacklist".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $to_uid 被拉黑用户的id
 * @property string $create_time
 */
class UserBlacklistModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_blacklist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'to_uid'], 'integer'],
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
            'user_id' => '用户id',
            'to_uid' => '被拉黑用户的id',
            'create_time' => 'Create Time',
        ];
    }
}
