<?php

namespace service\models;

use common\models\BusinessBackgroundModel;
use common\models\BusinessModel;
use common\models\BusinessPicModel;
use common\models\BusinessType;
use common\models\CityModel;
use common\models\ProvinceModel;

/**
 * This is the model class for table "business".
 *
 * @property int $id
 * @property int $b_id 背景图id
 * @property int $c_id 城市id
 * @property int $t_id 类型id
 * @property string $area 创业领域
 * @property string $description 创业描述
 * @property string $create_time 更新时间
 * @property string $update_time 更新时间
 * @property string $end_time 截止时间
 * @property int $status
 * @property int $is_del
 */
class Business extends BusinessModel
{

    public function fields()
    {
        $fields = parent::fields();

        $fields['type_name'] = function () {
            $type_name = BusinessType::find()
                ->select('name')
                ->where(['id' => $this->t_id])
                ->scalar();

            return $type_name ? $type_name : '';
        };

        $fields['city_name'] = function () {
            if ('000' == substr($this->c_id, -3)) {
                $model = new ProvinceModel();
            } else {
                $model = new CityModel();
            }

            $city_name = $model::find()
                ->select('name')
                ->where(['id' => $this->c_id])
                ->scalar();

            return $city_name ? $city_name : '';
        };

        $fields['background_image'] = function () {
            $background_image = BusinessBackgroundModel::find()
                ->select('img_url')
                ->where(['id' => $this->b_id])
                ->scalar();

            return $background_image ? $background_image : '';
        };

        $fields['pic_list'] = function () {
            $pic_list = BusinessPicModel::find()
                ->where(['b_id' => $this->id])
                ->asArray()
                ->all();

            return $pic_list ? array_column($pic_list, 'img_url') : [];
        };

        return $fields;
    }

}
