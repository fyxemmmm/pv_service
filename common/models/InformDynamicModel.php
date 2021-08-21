<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "inform_dynamic".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $inform_id inform表id
 * @property int $read 是否已读 0未读， 1已读
 * @property int $del 是否删除，1是已删除
 */
class InformDynamicModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inform_dynamic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'inform_id'], 'integer'],
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
            'inform_id' => 'inform表id'
        ];
    }
}
