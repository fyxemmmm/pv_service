<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "index_setting".
 *
 * @property int $id
 * @property string $index_banner_one 首页图1
 * @property string $index_banner_two 首页图2
 * @property string $index_banner_three 首页图3
 */
class IndexSettingModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'index_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['index_banner_one', 'index_banner_two', 'index_banner_three'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'index_banner_one' => '首页图1',
            'index_banner_two' => '首页图2',
            'index_banner_three' => '首页图3',
        ];
    }
}
