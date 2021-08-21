<?php

namespace service\models;

use common\models\InterestModel;
use common\models\InterestTypeModel;

/**
 * This is the model class for table "interest".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $pic 图片
 */
class Interest extends InterestModel
{

    public function fields()
    {
        $fields = parent::fields();

        $fields['type_name'] = function () {
            $type_name = InterestTypeModel::find()
                ->select('name')
                ->where(['id' => $this->t_id])
                ->scalar();

            return $type_name ? $type_name : '';
        };

        return $fields;
    }

}
