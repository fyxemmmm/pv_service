<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_card".
 *
 * @property int $id
 * @property int $u_id 用户id
 * @property string $name 姓名
 * @property string $company_name 公司名称
 * @property string $position 职位
 * @property string $mobile 手机号
 * @property string $wechat_username 微信号
 * @property int $b_id 背景图id
 */
class BusinessCardModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business_card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['u_id', 'b_id'], 'integer'],
            [['name', 'company_name', 'position', 'mobile', 'wechat_username'], 'string', 'max' => 255],
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
            'name' => '姓名',
            'company_name' => '公司名称',
            'position' => '职位',
            'mobile' => '手机号',
            'wechat_username' => '微信号',
            'b_id' => '背景图id',
        ];
    }
}
