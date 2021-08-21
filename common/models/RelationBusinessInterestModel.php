<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "relation_business_interest".
 *
 * @property int $b_id 创业id
 * @property int $u_id 用户id
 */
class RelationBusinessInterestModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'relation_business_interest';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['b_id', 'u_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'b_id' => '创业id',
            'u_id' => '用户id',
        ];
    }
}
