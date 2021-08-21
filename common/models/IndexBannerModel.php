<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "index_banner".
 *
 * @property int $id
 * @property string $img_url 图片地址
 * @property int $type 类型 0:文章 1:电台
 * @property int $data_id 数据id
 */
class IndexBannerModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'index_banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'data_id'], 'integer'],
            [['img_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'img_url' => '图片地址',
            'type' => '类型 0:文章 1:电台',
            'data_id' => '数据id',
        ];
    }
}
