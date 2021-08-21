<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lb_image".
 *
 * @property int $id
 * @property string $image_url
 * @property string $type 类型0 下款佣金，1 七日签到奖励，
 */
class LbImageModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lb_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image_url', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image_url' => 'Image Url',
            'type' => '类型0 下款佣金，1 七日签到奖励，',
        ];
    }
}
