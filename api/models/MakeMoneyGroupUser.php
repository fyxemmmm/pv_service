<?php

namespace api\models;

use common\models\MakeMoneyGroupUserModel;

/**
 * This is the model class for table "make_money_group_user".
 *
 * @property int $id
 * @property int $u_id 用户id
 * @property string $im_u_id 第三方用户id
 * @property string $im_group_id 第三方群id
 * @property string $create_time 创建时间
 */
class MakeMoneyGroupUser extends MakeMoneyGroupUserModel
{
    public function fields()
    {
        $fields = parent::fields();

        $fields['nick_name'] = function (){
            $nick_name = User::find()->select('nick_name')->where(['id' => $this->u_id])->scalar();
            return $nick_name ?? '';
        };

        $fields['avatar_image'] = function (){
            $avatar_image = User::find()->select('avatar_image')->where(['id' => $this->u_id])->scalar();
            return $avatar_image ?? '';
        };

        $fields['huanxin_username'] = function (){
            $huanxin_username = User::find()->select('huanxin_username')->where(['id' => $this->u_id])->scalar();
            return $huanxin_username ?? '';
        };

        return $fields;
    }
}
