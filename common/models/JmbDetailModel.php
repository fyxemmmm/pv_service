<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb_detail".
 *
 * @property int $id
 * @property int $jmb_id
 * @property int $type 类型：0电话联系
 * @property string $origin_price 非vip价
 * @property string $vip_price vip价
 * @property int $period 有效期 0永久
 * @property string $contact_mobile 联系电话
 * @property string $contact_person 联系人昵称
 * @property string $contact_person_job 联系人职位
 */
class JmbDetailModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jmb_id', 'type', 'period'], 'integer'],
            [['origin_price', 'vip_price'], 'number'],
            [['contact_mobile', 'contact_person', 'contact_person_job'], 'string', 'max' => 255],
            [['jmb_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jmb_id' => 'Jmb ID',
            'type' => '类型：0电话联系',
            'origin_price' => '非vip价',
            'vip_price' => 'vip价',
            'period' => '有效期 0永久',
            'contact_mobile' => '联系电话',
            'contact_person' => '联系人昵称',
            'contact_person_job' => '联系人职位',
        ];
    }
}
