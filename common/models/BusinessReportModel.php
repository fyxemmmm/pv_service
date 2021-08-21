<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_report".
 *
 * @property int $id
 * @property int $user_id 举报者 uid
 * @property int $business_id 被举报的创业id
 * @property string $content 举报的原因
 * @property string $create_time
 */
class BusinessReportModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'business_id'], 'integer'],
            [['create_time'], 'safe'],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '举报者 uid',
            'business_id' => '被举报的创业id',
            'content' => '举报的原因',
            'create_time' => 'Create Time',
        ];
    }
}
