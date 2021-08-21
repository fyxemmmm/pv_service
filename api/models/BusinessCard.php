<?php

namespace api\models;

use common\models\BusinessCardBackgroundModel;
use common\models\BusinessCardModel;

class BusinessCard extends BusinessCardModel
{
    public function fields()
    {
        $fields = parent::fields();

        $fields['background_img'] = function (){
            $background_img = BusinessCardBackgroundModel::find()->select('img_url')->where(['id' => $this->b_id])->scalar();
            return $background_img ? $background_img : '';
        };

        return $fields;
    }
}
