<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_relationship".
 *
 * @property int $id
 * @property int $leader_id 上家用户id
 * @property int $user_id 用户的id
 * @property string $create_time
 */
class UserRelationshipModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_relationship';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['leader_id', 'user_id'], 'integer'],
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
            'leader_id' => '上家用户id',
            'user_id' => '用户的id',
            'create_time' => 'Create Time',
        ];
    }
}
