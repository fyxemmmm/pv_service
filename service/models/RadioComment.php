<?php

namespace service\models;

use common\models\RadioCommentModel;
use common\models\RadioModel;
use common\models\UserModel;


class RadioComment extends RadioCommentModel
{
    public function fields()
    {
        $fields = parent::fields();

        $fields['nick_name'] = function () {
            $nick_name = UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar();
            return $nick_name ? $nick_name : '';
        };

        $fields['radio_name'] = function () {
            $radio_name = RadioModel::find()->select('title')->where(['id' => $this->radio_id])->scalar();
            return $radio_name ? $radio_name : '';
        };
        
        return $fields;
    }
}
