<?php

namespace api\models;

use common\models\WorkModel;
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
 * @property string $description
 * @property string $create_time 更新时间
 * @property string $update_time 更新时间
 * @property int $user_id 创建者
 * @property int $status 状态
 * @property int $is_recommend 推荐
 * @property int $is_del
 */
class Work extends WorkModel
{

    public function fields()
    {
        $fields = parent::fields();
        $fields['sub_desc'] = function () {
            return mb_substr($this->description, 0, 40);
        };

        $fields['nick_name'] = function () {
            $user = User::findOne($this->user_id);
            return $user ? $user->nick_name : '';
        };

        $fields['avatar_image'] = function () {
            $user = User::findOne($this->user_id);
            return $user ? $user->avatar_image : '';
        };

        $fields['create_time'] = function () {
            return date('Y年m月d日', strtotime($this->create_time));
        };

        return $fields;
    }
}
