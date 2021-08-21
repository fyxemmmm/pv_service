<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "inform_read".
 *
 * @property int $id
 * @property int $type 消息通知已读类型,0系统通知,1贷超订单通知,2收入通知,3团队通知
 * @property int $user_id
 * @property string $read_time 读取内容的时间
 */
class InformReadModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inform_read';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'user_id'], 'integer'],
            [['read_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '消息通知已读类型,0系统通知,1贷超订单通知,2收入通知,3团队通知',
            'user_id' => 'User ID',
            'read_time' => '读取内容的时间',
        ];
    }
}
