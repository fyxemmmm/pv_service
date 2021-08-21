<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "inform".
 *
 * @property int $id
 * @property int $dc_id 贷款产品id
 * @property int $radio_id 电台id
 * @property int $type 1贷超，2电台
 * @property string $url url
 * @property string $title 标题
 * @property string $icon icon
 * @property string $desc 描述文案
 * @property string $create_time
 */
class InformModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inform';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dc_id', 'radio_id', 'type'], 'integer'],
            [['create_time'], 'safe'],
            [['url', 'title', 'icon', 'desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dc_id' => '贷款产品id',
            'radio_id' => '电台id',
            'type' => '1贷超，2电台',
            'url' => 'url',
            'title' => '标题',
            'icon' => 'icon',
            'desc' => '描述文案',
            'create_time' => 'Create Time',
        ];
    }
}
