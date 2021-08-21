<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "work".
 *
 * @property int $id
 * @property string $company_name 公司名称
 * @property string $position_name 职位名称
 * @property string $position_type 职位类别
 * @property string $work_address 工作地址
 * @property string $salary_range 薪资范围
 * @property string $experience_requir 薪资范围
 * @property string $minimum_education 最低学历
 * @property string $skill_requirement 技能要求
 * @property string $description
 * @property string $create_time 更新时间
 * @property string $update_time 更新时间
 * @property int $user_id 创建者
 * @property int $status 状态
 * @property int $is_recommend 推荐
 * @property int $is_del
 */
class WorkModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['user_id', 'status', 'is_recommend', 'is_del'], 'integer'],
            [['company_name', 'position_name', 'position_type', 'salary_range', 'experience_requir', 'minimum_education', 'skill_requirement'], 'string', 'max' => 127],
            [['work_address'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => '公司名称',
            'position_name' => '职位名称',
            'position_type' => '职位类别',
            'work_address' => '工作地址',
            'salary_range' => '薪资范围',
            'experience_requir' => '薪资范围',
            'minimum_education' => '最低学历',
            'skill_requirement' => '技能要求',
            'description' => 'Description',
            'create_time' => '更新时间',
            'update_time' => '更新时间',
            'user_id' => '创建者',
            'status' => '状态',
            'is_recommend' => '推荐',
            'is_del' => 'Is Del',
        ];
    }
}
