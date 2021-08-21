<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "click_statistics".
 *
 * @property int $id
 * @property int $article 文章点击数
 * @property int $radio 电台点击数
 * @property int $business 创业邦点击数
 * @property int $make_money_group 赚钱圈点击数
 * @property int $type 0代表不去重的数据，1代表经过去重
 * @property string $time
 */
class ClickStatisticsModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'click_statistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article', 'radio', 'business', 'make_money_group', 'type'], 'integer'],
            [['time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article' => '文章点击数',
            'radio' => '电台点击数',
            'business' => '创业邦点击数',
            'make_money_group' => '赚钱圈点击数',
            'type' => '0代表不去重的数据，1代表经过去重',
            'time' => 'Time',
        ];
    }
}
