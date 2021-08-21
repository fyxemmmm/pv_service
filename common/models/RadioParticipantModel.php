<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "radio_participant".
 *
 * @property int $id
 * @property int $radio_id
 * @property int $user_id
 */
class RadioParticipantModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radio_participant';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['radio_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'radio_id' => 'Radio ID',
            'user_id' => 'User ID',
        ];
    }
}
