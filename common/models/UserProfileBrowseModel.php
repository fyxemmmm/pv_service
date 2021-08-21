<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_profile_browse".
 *
 * @property int $id
 * @property int $browse_user_id 浏览者id
 * @property int $user_id 用户id
 * @property string $create_time 创建时间
 * @property int $browse_user_ip 浏览者的ip
 */
class UserProfileBrowseModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_profile_browse';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['browse_user_id', 'create_time'], 'required'],
            [['browse_user_id', 'user_id', 'browse_user_ip'], 'integer'],
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
            'browse_user_id' => '浏览者id',
            'user_id' => '用户id',
            'create_time' => '创建时间',
            'browse_user_ip' => '浏览者的ip',
        ];
    }
}
