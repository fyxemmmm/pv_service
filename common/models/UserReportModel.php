<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_report".
 *
 * @property int $id
 * @property int $user_id 举报者 uid
 * @property int $to_uid 被举报用户的id
 * @property int $mkg_id 圈子id
 * @property string $content 举报的原因
 * @property string $create_time
 */
class UserReportModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'to_uid', 'mkg_id'], 'integer'],
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
            'to_uid' => '被举报用户的id',
            'mkg_id' => '圈子id',
            'content' => '举报的原因',
            'create_time' => 'Create Time',
        ];
    }
}
