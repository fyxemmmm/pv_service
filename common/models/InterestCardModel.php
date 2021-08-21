<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "interest_card".
 *
 * @property int $id
 * @property string $y_sku_id YHOUSE sku
 * @property string $name 卡名
 * @property string $pre_price 原价
 * @property string $now_price 原价
 * @property string $con_price 连续价
 * @property string $pic 卡图片地址
 * @property int $days 天数
 * @property int $unlimited_times 无限次数
 * @property int $status 状态
 */
class InterestCardModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'interest_card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pre_price', 'now_price', 'con_price'], 'number'],
            [['days', 'unlimited_times', 'status'], 'integer'],
            [['y_sku_id'], 'string', 'max' => 15],
            [['name'], 'string', 'max' => 127],
            [['pic'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'y_sku_id' => 'YHOUSE sku',
            'name' => '卡名',
            'pre_price' => '原价',
            'now_price' => '原价',
            'con_price' => '连续价',
            'pic' => '卡图片地址',
            'days' => '天数',
            'unlimited_times' => '无限次数',
            'status' => '状态',
        ];
    }
}
