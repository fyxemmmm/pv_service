<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "radio_label".
 *
 * @property int $id
 * @property int $radio_id
 * @property int $label_id
 */
class RadioLabelModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radio_label';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['radio_id', 'label_id'], 'integer'],
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
            'label_id' => 'Label ID',
        ];
    }
}
