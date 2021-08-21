<?php

namespace api\models;

use common\models\GoodsModel;
use common\models\GoodsSpecificationModel;

class GoodsIndex extends GoodsModel
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['discount']);

        $fields['dis_count'] = function (){
            return (string)$this->discount;
        };

        $fields['original_cost'] = function (){
            $sql = 'SELECT MIN(original_cost) AS original_cost FROM goods_specification WHERE goods_id=:goods_id';
            return GoodsSpecificationModel::findBySql($sql, [':goods_id' => $this->id])->scalar() ?: "0.00";
        };

        $fields['after_discount_cost'] = function (){
            $sql = 'SELECT MIN(after_discount_cost) AS original_cost FROM goods_specification WHERE goods_id=:goods_id';
            return GoodsSpecificationModel::findBySql($sql, [':goods_id' => $this->id])->scalar() ?: "0.00";
        };

        $fields['sale_num'] = function () {
            $y_num = $this->id % 7;
            !$y_num && $y_num = 1;
            $days = ceil((time() - strtotime($this->create_time)) / 86400);
            return $y_num + $days;
        };

        return $fields;
    }

}
