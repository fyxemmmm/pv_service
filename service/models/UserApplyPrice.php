<?php

namespace service\models;

use common\models\UserApplyPriceModel;
use common\models\UserModel;
use common\models\UserCardModel;

class UserApplyPrice extends UserApplyPriceModel
{

    public function fields()
    {
        $fields = parent::fields();
        $fields['nick_name'] = function () {
            return UserModel::find()->select('nick_name')->where(['id' => $this->user_id])->scalar() ?: '';
        };

        $fields['zfb_account'] = function () {
            return UserCardModel::find()->select('zfb_account')->where(['id' => $this->user_card_id])->scalar() ?: '';
        };

        $fields['zfb_receive_name'] = function () {
            return UserCardModel::find()->select('zfb_receive_name')->where(['id' => $this->user_card_id])->scalar() ?: '';
        };

        $fields['zfb_receive_mobile'] = function () {
            return UserCardModel::find()->select('zfb_receive_mobile')->where(['id' => $this->user_card_id])->scalar() ?: '';
        };

        $fields['yhk_mobile'] = function () {
            return UserCardModel::find()->select('yhk_mobile')->where(['id' => $this->user_card_id])->scalar() ?: '';
        };

        $fields['yhk_number'] = function () {
            return UserCardModel::find()->select('yhk_number')->where(['id' => $this->user_card_id])->scalar() ?: '';
        };

        $fields['yhk_name'] = function () {
            return UserCardModel::find()->select('yhk_name')->where(['id' => $this->user_card_id])->scalar() ?: '';
        };

        return $fields;
    }

}
