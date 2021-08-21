<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "radio_like".
 *
 * @property int $id
 * @property int $user_id
 * @property int $radio_id
 */
class RadioLikeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radio_like';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'radio_id'], 'integer'],
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
            'radio_id' => 'Radio ID',
        ];
    }
}
