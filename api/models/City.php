<?php

namespace api\models;

use common\models\CityModel;
use common\models\ProvinceModel;

/**
 * This is the model class for table "city".
 *
 * @property int $id
 * @property string $name
 * @property int $pid
 */
class City extends CityModel
{

    public function fields()
    {
        $fields = parent::fields();

//        $fields['name'] = function (){
//            if ('86' != $this->pid) {
//                $province_name = ProvinceModel::findOne($this->pid)->getAttribute('name');
//                return $province_name . '·' . $this->name;
//            } else {
//                return $this->name;
//            }
//        };

        return $fields;
    }

}
