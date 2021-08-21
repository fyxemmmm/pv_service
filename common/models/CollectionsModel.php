<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "collections".
 *
 * @property int $id
 * @property int $user_id
 * @property int $item_id
 * @property int $type 0文章， 1加盟宝
 * @property string $create_time
 */
class CollectionsModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collections';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'item_id', 'type'], 'integer'],
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
            'item_id' => 'Item ID',
            'type' => '0文章， 1加盟宝',
            'create_time' => 'Create Time',
        ];
    }
}
