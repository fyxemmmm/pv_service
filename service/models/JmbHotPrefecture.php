<?php

namespace service\models;

use common\models\JmbHotCategoryModel;
use common\models\JmbCategoryModel;

class JmbHotPrefecture extends JmbHotCategoryModel
{

    public function fields()
    {
        $fields = parent::fields();

        $fields['first_cate_id'] = function(){
            $pid = JmbCategoryModel::find()->select('pid')->where(['id' => $this->jmb_category_id])->scalar();
            return (int)JmbCategoryModel::find()->select('id')->where(['id' => $pid])->scalar();
        };

        return $fields;
    }

}
