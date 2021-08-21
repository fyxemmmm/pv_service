<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_order".
 *
 * @property int $id
 * @property int $jmb_id
 * @property int $user_id
 * @property string $order_sign
 * @property int $status
 * @property string $create_time
 * @property string $update_time
 */
class JmbOrderModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jmb_id', 'user_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['order_sign'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jmb_id' => 'Jmb ID',
            'user_id' => 'User ID',
            'order_sign' => 'Order Sign',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
