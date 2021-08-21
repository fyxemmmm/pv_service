<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "make_money_group_apply".
 *
 * @property int $id
 * @property int $u_id 用户id
 * @property string $im_u_id 第三方用户id
 * @property string $im_group_id 第三方群id
 * @property string $create_time 创建时间
 * @property int $status 状态
 */
class MakeMoneyGroupApplyModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'make_money_group_apply';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_id', 'im_group_id', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['im_u_id'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'u_id' => '用户id',
            'im_u_id' => '第三方用户id',
            'im_group_id' => '第三方群id',
            'create_time' => '创建时间',
            'status' => '状态',
        ];
    }
}
