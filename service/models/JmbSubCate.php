<?php

namespace service\models;

use common\models\JmbCategoryModel;

class JmbSubCate extends JmbCategoryModel
{

    public function fields()
    {
        $fields = parent::fields();
        $fields['parent_cate_name'] = function (){
            return JmbCategoryModel::find()->select('name')->where(['id' => $this->pid])->scalar();
        };


        return $fields;
    }

}
