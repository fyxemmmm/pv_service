<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_id_card_no".
 *
 * @property int $id
 * @property int $user_id
 * @property string $real_name 真实姓名
 * @property string $id_card_no 身份证号码
 * @property string $create_time
 */
class UserIdCardNoModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_id_card_no';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['real_name'], 'string', 'max' => 50],
            [['id_card_no'], 'string', 'max' => 30],
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
            'real_name' => '真实姓名',
            'id_card_no' => '身份证号码',
            'create_time' => 'Create Time',
        ];
    }
}
