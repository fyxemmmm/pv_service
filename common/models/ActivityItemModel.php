<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_item".
 *
 * @property int $id
 * @property int $activity_id 活动id
 * @property string $price 项目价格
 */
class ActivityItemModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id'], 'integer'],
            [['price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => '活动id',
            'price' => '项目价格',
        ];
    }
}
