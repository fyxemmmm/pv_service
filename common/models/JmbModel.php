<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jmb".
 *
 * @property int $id
 * @property string $name 加盟宝名称
 * @property int $jmb_category_id 分类id
 * @property string $brand_name 品牌名称
 * @property string $desc 描述
 * @property int $direct_store_num 直营店数量
 * @property int $join_store_num 加盟店数量
 * @property int $apply_num 申请加盟数量
 * @property int $buy_num 有多少个人购买了联系方式
 * @property string $main_project 主营项目
 * @property string $register_time 品牌注册时间
 * @property string $location 公司所在地
 * @property string $est_init_investment 预估初始投资
 * @property string $est_customer_unit_price 预估客单价
 * @property string $est_customer_daily_flow 预估日客流量
 * @property string $est_mothly_sale 预估月销售额
 * @property string $est_gross_profit 预估毛利润
 * @property string $est_payback_period 预估回报周期
 * @property string $inital_fee 初期预计投入
 * @property string $join_fee 加盟费
 * @property string $deposit_fee 保证金
 * @property string $device_fee 设备费用
 * @property string $other_fee 其他费用
 * @property int $is_hot_join 热门分类中的名牌加盟 1是 0否
 * @property int $is_hot_recommend 是否是首页热门推荐 1是 0否
 * @property string $image_url 背景图片
 * @property string $info 富文本内容
 * @property int $status 状态1启用 0禁用
 * @property int $del 1已删除
 * @property string $create_time
 * @property string $update_time
 * @property string $delete_time
 */
class JmbModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jmb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jmb_category_id', 'direct_store_num', 'join_store_num', 'apply_num', 'buy_num', 'is_hot_join', 'is_hot_recommend', 'status', 'del'], 'integer'],
            [['info'], 'string'],
            [['create_time', 'update_time', 'delete_time'], 'safe'],
            [['name', 'brand_name', 'desc', 'main_project', 'register_time', 'location', 'est_init_investment', 'est_customer_unit_price', 'est_customer_daily_flow', 'est_mothly_sale', 'est_gross_profit', 'est_payback_period', 'inital_fee', 'join_fee', 'deposit_fee', 'device_fee', 'other_fee', 'image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '加盟宝名称',
            'jmb_category_id' => '分类id',
            'brand_name' => '品牌名称',
            'desc' => '描述',
            'direct_store_num' => '直营店数量',
            'join_store_num' => '加盟店数量',
            'apply_num' => '申请加盟数量',
            'buy_num' => '有多少个人购买了联系方式',
            'main_project' => '主营项目',
            'register_time' => '品牌注册时间',
            'location' => '公司所在地',
            'est_init_investment' => '预估初始投资',
            'est_customer_unit_price' => '预估客单价',
            'est_customer_daily_flow' => '预估日客流量',
            'est_mothly_sale' => '预估月销售额',
            'est_gross_profit' => '预估毛利润',
            'est_payback_period' => '预估回报周期',
            'inital_fee' => '初期预计投入',
            'join_fee' => '加盟费',
            'deposit_fee' => '保证金',
            'device_fee' => '设备费用',
            'other_fee' => '其他费用',
            'is_hot_join' => '热门分类中的名牌加盟 1是 0否',
            'is_hot_recommend' => '是否是首页热门推荐 1是 0否',
            'image_url' => '背景图片',
            'info' => '富文本内容',
            'status' => '状态1启用 0禁用',
            'del' => '1已删除',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'delete_time' => 'Delete Time',
        ];
    }
}
