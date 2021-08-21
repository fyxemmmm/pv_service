<?php

namespace api\models;

use common\models\JmbHotCategoryModel;

class JmbHotCategory extends JmbHotCategoryModel
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['id'] = function (){
            return (string)$this->id;
        };
        $fields['jmb_category_id'] = function (){
            return (string)$this->jmb_category_id;
        };
        return $fields;
    }
}
