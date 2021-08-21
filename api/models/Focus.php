<?php

namespace api\models;
use common\models\FocusModel;
use common\models\UserModel;

class Focus extends FocusModel
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['focus_user_id']);
        unset($fields['user_id']);
        unset($fields['id']);
//        unset($fields['read']);
        $fields['nick_name'] = function (){
            $data = UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar();
            return $data ? : '';
        };
        $fields['avatar_image'] = function (){
            $data = UserModel::find()->select('avatar_image')->where(['id' => $this->user_id])->scalar();
            return $data ? : '';
        };
        $fields['fans_id'] = function (){
            return $this->user_id;
        };

        return $fields;
    }

}
