<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "index_banner_xijing".
 *
 * @property int $id
 * @property string $img_url 图片地址
 * @property string $web_url h5地址
 * @property int $type 类型 0:商品 1:h5
 * @property int $g_id 数据id
 */
class IndexBannerXijingModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'index_banner_xijing';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'g_id'], 'integer'],
            [['img_url', 'web_url'], 'string', 'max' => 255],
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
            'web_url' => 'h5地址',
            'type' => '类型 0:商品 1:h5',
            'g_id' => '数据id',
        ];
    }
}
