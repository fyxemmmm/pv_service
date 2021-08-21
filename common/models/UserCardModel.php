<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_card".
 *
 * @property int $id
 * @property int $type 0支付宝 1银行卡
 * @property int $user_id
 * @property string $zfb_account 支付宝账号
 * @property string $zfb_receive_name 支付宝收款人姓名
 * @property string $zfb_receive_mobile 支付宝收款人手机号
 * @property string $yhk_mobile 银行卡绑定的手机号
 * @property string $yhk_number 银行卡号码
 * @property string $yhk_name 所属银行
 * @property string $create_time
 */
class UserCardModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type', 'user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['zfb_account', 'zfb_receive_name', 'zfb_receive_mobile', 'yhk_mobile', 'yhk_number', 'yhk_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '0支付宝 1银行卡',
            'user_id' => 'User ID',
            'zfb_account' => '支付宝账号',
            'zfb_receive_name' => '支付宝收款人姓名',
            'zfb_receive_mobile' => '支付宝收款人手机号',
            'yhk_mobile' => '银行卡绑定的手机号',
            'yhk_number' => '银行卡号码',
            'yhk_name' => '所属银行',
            'create_time' => 'Create Time',
        ];
    }
}
