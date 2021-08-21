<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business".
 *
 * @property int $id
 * @property int $u_id 创建者，用户id
 * @property int $b_id 背景图id
 * @property int $c_id 城市id
 * @property int $t_id 类型id
 * @property string $area 创业领域
 * @property string $description 创业描述
 * @property int $interested_nums 感兴趣数
 * @property string $create_time 更新时间
 * @property string $update_time 更新时间
 * @property string $end_time 截止时间
 * @property int $status
 * @property int $is_del
 * @property int $is_pri
 */
class BusinessModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_id', 'b_id', 'c_id', 't_id', 'interested_nums', 'status', 'is_del', 'is_pri'], 'integer'],
            [['create_time', 'update_time', 'end_time'], 'safe'],
            [['end_time'], 'required'],
            [['area', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'u_id' => '创建者，用户id',
            'b_id' => '背景图id',
            'c_id' => '城市id',
            't_id' => '类型id',
            'area' => '创业领域',
            'description' => '创业描述',
            'interested_nums' => '感兴趣数',
            'create_time' => '更新时间',
            'update_time' => '更新时间',
            'end_time' => '截止时间',
            'status' => 'Status',
            'is_del' => 'Is Del',
            'is_pri' => 'Is Pri',
        ];
    }
}
