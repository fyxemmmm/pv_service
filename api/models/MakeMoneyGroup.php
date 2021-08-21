<?php

namespace api\models;

use common\models\AppImageModel;
use common\models\MakeMoneyGroupApplyModel;
use common\models\MakeMoneyGroupModel;
use common\models\MakeMoneyTypeModel;

/**
 * This is the model class for table "make_money_group".
 *
 * @property int $id
 * @property int $u_id 用户id
 * @property int $type_id 类型id
 * @property string $name 名称
 * @property string $logo logo
 * @property int $nums 群人数
 * @property int $upvote_nums 点赞人数
 * @property string $description 描述
 * @property string $announcement 公告
 * @property string $im_group_id 群组id
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property int $join_need_consent 加入需要同意
 * @property int $status 状态
 */
class MakeMoneyGroup extends MakeMoneyGroupModel
{
    public static $user_id;
    private static $top_keys = [
        'mmg_b_top1',
        'mmg_b_top2',
        'mmg_b_top3',
        'mmg_b_top4'
    ];

    public function fields()
    {
        $fields = parent::fields();

        $fields['is_new'] = function () {
            return (strtotime("-1 months") > strtotime($this->create_time)) ? 0 : 1;
        };

        $fields['join_status'] = function () {
            $join_status = 0;
            $apply_exists = MakeMoneyGroupApplyModel::findOne(['u_id' => self::$user_id, 'im_group_id' => $this->im_group_id, 'status' => 0]);
            if ($apply_exists) {
                $join_status = 2;
            } else {
                $join_exists = MakeMoneyGroupUser::findOne(['u_id' => self::$user_id, 'im_group_id' => $this->im_group_id]);
                if ($join_exists) {
                    $join_status = 1;
                }
            }

            return $join_status;
        };

        $fields['top_pic'] = function () {
            $key = array_shift(self::$top_keys);
            $pic = AppImageModel::find()->select('pic')->where(['key' => $key])->scalar();
            return $pic ?: '';
        };

        $fields['type_name'] = function () {
            $type_name = MakeMoneyTypeModel::find()->select('name')->where(['id' => $this->type_id])->scalar();
            return $type_name ?: '';
        };

        return $fields;
    }
}
