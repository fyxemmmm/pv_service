<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_user".
 *
 * @property int $id
 * @property int $user_id
 * @property int $jmb_id
 * @property int $status 是否可用 1可用
 * @property int $period 0是永久
 * @property string $create_time
 */
class JmbUserModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'jmb_id', 'status', 'period'], 'integer'],
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
            'user_id' => 'User ID',
            'jmb_id' => 'Jmb ID',
            'status' => '是否可用 1可用',
            'period' => '0是永久',
            'create_time' => 'Create Time',
        ];
    }
}
