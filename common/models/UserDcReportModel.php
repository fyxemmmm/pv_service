<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_dc_report".
 *
 * @property int $id
 * @property string $order_sign 单号
 * @property string $name 下款人姓名
 * @property string $mobile 下款人手机号
 * @property int $leader_id 上家用户id
 * @property int $user_id 下款人用户id(下家用户id)
 * @property string $loan_date 放款日期
 * @property string $pay_money 下款金额
 * @property string $return_money 预计奖励金
 * @property int $product_id 产品id
 * @property string $product_name 产品名
 * @property string $product_image 产品图片
 * @property string $image_url_1 初审额度出现的页面截图
 * @property string $image_url_2 下款人APP个人中心截图
 * @property string $image_url_3 到账短信截图
 * @property string $image_url_4 账单截图
 * @property string $fail_reason 报备失败的理由
 * @property int $status 报备状态 0报备审核中 1报备完成 2报备失败
 * @property string $create_time
 * @property string $update_time
 */
class UserDcReportModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_dc_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['leader_id', 'user_id', 'product_id', 'status'], 'integer'],
            [['loan_date', 'create_time', 'update_time'], 'safe'],
            [['pay_money', 'return_money'], 'number'],
            [['order_sign', 'name', 'product_name', 'product_image', 'image_url_1', 'image_url_2', 'image_url_3', 'image_url_4', 'fail_reason'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_sign' => '单号',
            'name' => '下款人姓名',
            'mobile' => '下款人手机号',
            'leader_id' => '上家用户id',
            'user_id' => '下款人用户id(下家用户id)',
            'loan_date' => '放款日期',
            'pay_money' => '下款金额',
            'return_money' => '预计奖励金',
            'product_id' => '产品id',
            'product_name' => '产品名',
            'product_image' => '产品图片',
            'image_url_1' => '初审额度出现的页面截图',
            'image_url_2' => '下款人APP个人中心截图',
            'image_url_3' => '到账短信截图',
            'image_url_4' => '账单截图',
            'fail_reason' => '报备失败的理由',
            'status' => '报备状态 0报备审核中 1报备完成 2报备失败',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
