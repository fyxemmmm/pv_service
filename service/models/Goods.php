<?php

namespace service\models;
use common\models\GoodsModel;

class Goods extends GoodsModel
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['detail_content'] = function(){
            return mb_convert_encoding(htmlspecialchars_decode($this->detail_content), 'UTF-8', 'UTF-8');
        };
        
        return $fields;
    }

}
